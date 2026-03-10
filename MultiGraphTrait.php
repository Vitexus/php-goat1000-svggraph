<?php

declare(strict_types=1);

/**
 * This file is part of the SVGGraph package
 *
 * https://www.goat1000.com/svggraph.php
 *
 * (c) Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * For more information, please contact <graham@goat1000.com>.
 */

namespace Goat1000\SVGGraph;

/**
 * Implements MultiGraph setup and overrides some Graph functions.
 */
trait MultiGraphTrait
{
    protected function setup(): void
    {
        $dataset_count = \count($this->multi_graph);
        $this->colourSetup($this->multi_graph->itemsCount(-1), $dataset_count);
    }

    /**
     * Construct MultiGraph when setting values.
     *
     * @param mixed $values
     */
    public function values($values): void
    {
        parent::values($values);

        if (!$this->values->error) {
            $this->multi_graph = new MultiGraph(
                $this->values,
                $this->getOption('force_assoc'),
                $this->getOption('datetime_keys'),
                $this->getOption('require_integer_keys'),
            );

            $this->multi_graph->setEnabledDatasets($this->getOption('dataset'));
        }
    }

    public function getMinValue()
    {
        return $this->multi_graph->getMinValue();
    }

    public function getMaxValue()
    {
        return $this->multi_graph->getMaxValue();
    }

    public function getMinKey()
    {
        return $this->multi_graph->getMinKey();
    }

    public function getMaxKey()
    {
        return $this->multi_graph->getMaxKey();
    }

    public function getKey($i)
    {
        return $this->multi_graph->getKey($i);
    }
}
