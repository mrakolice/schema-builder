<?php
declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Cycle\Schema\Relation;

use Cycle\ORM\Relation;
use Cycle\Schema\Registry;
use Cycle\Schema\Relation\Traits\FieldTrait;
use Cycle\Schema\Relation\Traits\ForeignKeyTrait;

class BelongsTo extends RelationSchema
{
    use FieldTrait, ForeignKeyTrait;

    protected const RELATION_TYPE = Relation::BELONGS_TO;

    protected const OPTION_SCHEMA = [
        // save with parent
        Relation::CASCADE            => true,

        // use outer entity constrain by default
        Relation::CONSTRAIN          => true,

        // nullable by default
        Relation::NULLABLE           => true,

        // link to parent entity primary key by default
        Relation::INNER_KEY          => '{relation}_{outerKey}',

        // default field name for inner key
        Relation::OUTER_KEY          => '{target:primaryKey}',

        // rendering options
        RelationSchema::FK_CREATE    => true,
        RelationSchema::FK_ACTION    => 'CASCADE',
        RelationSchema::INDEX_CREATE => true,
    ];

    /**
     * @param Registry $registry
     */
    public function compute(Registry $registry)
    {
        parent::compute($registry);

        $source = $registry->getEntity($this->source);
        $target = $registry->getEntity($this->target);

        // create target outer field
        $this->ensureField(
            $source,
            $this->options->get(Relation::INNER_KEY),
            $this->getField($target, Relation::OUTER_KEY)
        );
    }

    /**
     * @param Registry $registry
     */
    public function render(Registry $registry)
    {
        $source = $registry->getEntity($this->source);
        $target = $registry->getEntity($this->target);

        $innerField = $this->getField($source, Relation::INNER_KEY);
        $outerField = $this->getField($target, Relation::OUTER_KEY);

        $table = $registry->getTableSchema($source);

        if ($this->options->get(self::INDEX_CREATE)) {
            $table->index([$innerField->getColumn()]);
        }

        if ($this->options->get(self::FK_CREATE)) {
            $this->createForeignKey($registry, $target, $source, $outerField, $innerField);
        }
    }
}