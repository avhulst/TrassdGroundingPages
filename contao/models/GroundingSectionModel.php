<?php

declare(strict_types=1);

namespace Contao;

/**
 * @property int         $id
 * @property int         $pid
 * @property int         $sorting
 * @property int         $tstamp
 * @property string      $sectionType
 * @property string      $sectionTitle
 * @property string|null $factGrid
 * @property string|null $timelineItems
 * @property string|null $definedTerms
 * @property string|null $faqItems
 * @property string|null $sources
 * @property string|null $identifiers
 * @property string       $published
 *
 * @method static self|null findById(int|string $id, array $options = [])
 * @method static Model\Collection<GroundingSectionModel>|null findByPid(int|string $pid, array $options = [])
 */
class GroundingSectionModel extends Model
{
    protected static $strTable = 'tl_grounding_section';
}
