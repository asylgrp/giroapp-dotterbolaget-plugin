<?php

declare(strict_types = 1);

namespace byrokrat\GiroappDotterbolagetPlugin;

use byrokrat\giroapp\Filter\FilterInterface;
use byrokrat\giroapp\Formatter\FormatterInterface;
use byrokrat\giroapp\Model\Donor;
use byrokrat\giroapp\Plugin\Plugin;
use Symfony\Component\Console\Output\OutputInterface;

class DotterbolagetFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'dotterbolaget';
    }

    public function filterDonor(Donor $donor): bool
    {
        if (!$donor->getState()->isActive()) {
            return false;
        }

        foreach ($donor->getAttributes() as $key => $value) {
            if (preg_match('/^dotterbolaget$/i', $key) {
                return !!$value;
            }
        }

        return !!preg_match('/dotterbolaget/i', $donor->getComment());
    }
}

class DotterbolagetFormatter implements FormatterInterface
{
    /**
     * Number of addresses on each line in output
     */
    const ITEMS_ON_LINE = 5;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string[]
     */
    private $addresses;

    public function getName(): string
    {
        return 'dotterbolaget';
    }

    public function initialize(OutputInterface $output): void
    {
        $this->output = $output;
        $this->addresses = [];
    }

    public function formatDonor(Donor $donor): void
    {
        $address = array_filter([
            $donor->getName(),
            $donor->getPostalAddress()->getLine1(),
            $donor->getPostalAddress()->getLine2(),
            $donor->getPostalAddress()->getLine3(),
            "{$donor->getPostalAddress()->getPostalCode()} {$donor->getPostalAddress()->getPostalCity()}"
        ]);

        $this->addresses[$donor->getName()] = implode(PHP_EOL, $address);
    }

    public function finalize(): void
    {
        ksort($this->addresses);

        foreach (array_chunk($this->addresses, self::ITEMS_ON_LINE) as $chunk) {
            $this->output->writeln('"' . implode('", "', $chunk) . '"');
        }
    }
}

return new Plugin(
    new DotterbolagetFilter,
    new DotterbolagetFormatter
);
