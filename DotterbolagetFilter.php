<?php

declare(strict_types = 1);

namespace byrokrat\GiroappDotterbolagetPlugin;

use byrokrat\giroapp\Filter\FilterInterface;
use byrokrat\giroapp\States;
use byrokrat\giroapp\Model\Donor;

class DotterbolagetFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'dotterbolaget';
    }

    public function filterDonor(Donor $donor): bool
    {
        return !!preg_match('/dotterbolaget/i', $donor->getComment());
    }
}
