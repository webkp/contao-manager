<?php

/*
 * This file is part of Contao Manager.
 *
 * (c) Contao Association
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\ManagerApi\IntegrityCheck;

use Contao\ManagerApi\I18n\Translator;

abstract class AbstractIntegrityCheck implements IntegrityCheckInterface
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Translates a string from the "integrity" domain.
     *
     * @param string $id
     * @param array  $params
     *
     * @return string
     */
    protected function trans($id, array $params = [])
    {
        return $this->translator->trans('integrity.'.$id, $params);
    }
}
