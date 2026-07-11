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

    public static function findPublishedByAlias(string $alias): ?self
    {
        return static::findOneBy(['alias=?', 'published=?'], [$alias, '1']);
    }

    /**
     * @return Model\Collection<GroundingSectionModel>|null
     */
    public function getPublishedSections(): ?Model\Collection
    {
        return GroundingSectionModel::findBy(
            ['pid=?', 'published=?'],
            [$this->id, '1'],
            ['order' => 'sorting ASC'],
        );
    }
}
