<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\EventListener\DataContainer;

use Contao\DC_Table;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Trassd\Contao\GroundingPages\EventListener\DataContainer\ReviewFieldsListener;
use Trassd\Contao\GroundingPages\Review\ReviewFieldsValidator;

#[AllowMockObjectsWithoutExpectations]
class ReviewFieldsListenerTest extends ContaoTestCase
{
    public function testNameWithoutDateInPostIsRejected(): void
    {
        $listener = $this->listener(['reviewerName' => 'Jane Doe', 'lastReviewed' => '']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.reviewIncomplete');

        $listener->onSaveReviewerName('Jane Doe', $this->dc());
    }

    public function testDateWithoutNameInPostIsRejected(): void
    {
        $listener = $this->listener(['reviewerName' => '', 'lastReviewed' => '22.10.2026']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.reviewIncomplete');

        $listener->onSaveLastReviewed(time(), $this->dc());
    }

    public function testFutureDateIsRejected(): void
    {
        $listener = $this->listener(['reviewerName' => 'Jane Doe']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.reviewInFuture');

        $listener->onSaveLastReviewed(time() + 2 * 86_400, $this->dc());
    }

    public function testCompletePairIsReturnedUnchanged(): void
    {
        $listener = $this->listener(['reviewerName' => 'Jane Doe', 'lastReviewed' => '22.10.2026']);
        $timestamp = time() - 86_400;

        $this->assertSame('Jane Doe', $listener->onSaveReviewerName('Jane Doe', $this->dc()));
        $this->assertSame($timestamp, $listener->onSaveLastReviewed($timestamp, $this->dc()));
    }

    public function testEmptyPairIsAccepted(): void
    {
        $listener = $this->listener(['reviewerName' => '', 'lastReviewed' => '']);

        $this->assertSame('', $listener->onSaveReviewerName('', $this->dc()));
        $this->assertNull($listener->onSaveLastReviewed(null, $this->dc()));
    }

    public function testEditAllFieldNamesWithRecordIdSuffixAreRead(): void
    {
        // „Mehrere bearbeiten“ sendet die Felder als <feld>_<id>.
        $listener = $this->listener(['reviewerName_42' => 'Jane Doe', 'lastReviewed_42' => '22.10.2026']);

        $this->assertSame('Jane Doe', $listener->onSaveReviewerName('Jane Doe', $this->dc(42)));
    }

    public function testFallsBackToStoredRecordWhenSiblingIsNotPosted(): void
    {
        $listener = $this->listener([]);
        $dc = $this->dc(1, ['reviewerName' => '', 'lastReviewed' => (string) (time() - 86_400)]);

        $this->assertSame('Jane Doe', $listener->onSaveReviewerName('Jane Doe', $dc));
    }

    /**
     * @param array<string, string> $post
     */
    private function listener(array $post): ReviewFieldsListener
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([], $post));

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnArgument(0)
        ;

        return new ReviewFieldsListener(new ReviewFieldsValidator(), $requestStack, $translator);
    }

    /**
     * @param array<string, string|null> $stored
     */
    private function dc(int $id = 1, array $stored = ['reviewerName' => '', 'lastReviewed' => null]): DC_Table
    {
        return $this->mockClassWithProperties(DC_Table::class, [
            'id' => $id,
            'activeRecord' => (object) $stored,
        ]);
    }
}
