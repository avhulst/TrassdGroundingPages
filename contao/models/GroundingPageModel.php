<?php

declare(strict_types=1);

namespace Contao;

/**
 * @property int         $id
 * @property int         $tstamp
 * @property string      $title
 * @property string      $alias
 * @property string      $language
 * @property string      $entityType
 * @property string      $customSchemaType
 * @property string      $definition
 * @property string      $segment
 * @property string      $distinction
 * @property string|null $sameAs
 * @property string      $publisher
 * @property string      $maintainer
 * @property string      $status
 * @property string      $entryVersion
 * @property string|int  $datePublished
 * @property string|int  $dateVerified
 * @property string|null $changelog
 * @property string      $correctionContact
 * @property string       $published
 *
 * @method static self|null findById(int|string $id, array $options = [])
 * @method static self|null findByPk(int|string $id, array $options = [])
 * @method static self|null findOneBy(array $column, array $value, array $options = [])
 */
class GroundingPageModel extends Model
{
    protected static $strTable = 'tl_grounding_page';

    /**
     * Findet eine veröffentlichte Grounding Page. Im Frontend-Vorschaumodus wird – wie
     * bei den Core-Models – auch eine unveröffentlichte geliefert.
     */
    public static function findPublishedById(int $id, array $options = []): ?self
    {
        $columns = ['id=?'];
        $values = [$id];

        if (!static::isPreviewMode($options)) {
            $columns[] = 'published=?';
            $values[] = '1';
        }

        return static::findOneBy($columns, $values, $options);
    }

    public static function findPublishedByAlias(string $alias): ?self
    {
        return static::findOneBy(['alias=?', 'published=?'], [$alias, '1']);
    }

    /**
     * @return Model\Collection<GroundingSectionModel>|null
     */
    public function getPublishedSections(array $options = []): ?Model\Collection
    {
        $columns = ['pid=?'];
        $values = [$this->id];

        // Im Vorschaumodus auch unveröffentlichte Sections zeigen, passend zu findPublishedById().
        if (!static::isPreviewMode($options)) {
            $columns[] = 'published=?';
            $values[] = '1';
        }

        return GroundingSectionModel::findBy($columns, $values, ['order' => 'sorting ASC', ...$options]);
    }
}
