<?php

/**
 * Copyright 2023 Christoph M. Becker
 *
 * This file is part of Extedit_XH.
 *
 * Extedit_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Extedit_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Extedit_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Extedit\Infra;

class FakeContentRepo extends ContentRepo
{
    private $options;

    public function options(array $options)
    {
        $this->options = $options;
    }

    public function save(string $name, string $content): bool
    {
        if (isset($this->options["save"]) && $this->options["save"] === false) {
            return false;
        }
        return parent::save($name, $content);
    }

    public function findLastModification(string $name): int
    {
        return $this->options["lastModification"] ?? 0;
    }
}
