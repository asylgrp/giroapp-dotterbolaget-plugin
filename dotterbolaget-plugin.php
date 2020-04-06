<?php

declare(strict_types = 1);

namespace byrokrat\GiroappDotterbolagetPlugin;

use byrokrat\giroapp\Filter\FilterInterface;
use byrokrat\giroapp\Formatter\FormatterInterface;
use byrokrat\giroapp\Domain\Donor;
use byrokrat\giroapp\Domain\State;
use byrokrat\giroapp\Plugin\Plugin;
use byrokrat\giroapp\Plugin\ApiVersionConstraint;
use Symfony\Component\Console\Output\OutputInterface;

class DotterbolagetFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'db';
    }

    public function filterDonor(Donor $donor): bool
    {
        if (!$this->isValidDonorState($donor)) {
            return false;
        }

        foreach ($donor->getAttributes() as $key => $value) {
            if (preg_match('/^dotterbolaget$/i', $key)) {
                return !!$value;
            }
        }

        return !!preg_match('/dotterbolaget/i', $donor->getComment());
    }

    private function isValidDonorState(Donor $donor): bool
    {
        $state = $donor->getState();

        return $state instanceof State\Active
            || $state instanceof State\AwaitingTransactionRegistration
            || $state instanceof State\MandateSent
            || $state instanceof State\NewDigitalMandate
            || $state instanceof State\NewMandate
            || $state instanceof State\TransactionRegistrationSent;
    }
}

class DotterbolagetFormatter implements FormatterInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function getName(): string
    {
        return 'db';
    }

    public function initialize(OutputInterface $output): void
    {
        $this->output = $output;
        $this->output->writeln('"Namn","Adress","Postnummer","Ort"');
    }

    public function formatDonor(Donor $donor): void
    {
        $address = trim(
            sprintf(
                "%s\n%s\n%s",
                $donor->getPostalAddress()->getLine1(),
                $donor->getPostalAddress()->getLine2(),
                $donor->getPostalAddress()->getLine3()
            )
        );

        $this->output->writeln(
            sprintf(
                '"%s","%s","%s","%s"',
                $donor->getName(),
                $address,
                self::formatPostalCode($donor),
                $donor->getPostalAddress()->getPostalCity()
            )
        );
    }

    public function finalize(): void
    {
    }

    private static function formatPostalCode(Donor $donor): string
    {
        $code = $donor->getPostalAddress()->getPostalCode();

        if (strlen($code) != 5) {
            throw new \Exception("Postal code '$code' for donor '{$donor->getName()}' does not contain 5 characters");
        }

        return substr($code, 0, 3) . ' ' . substr($code, -2);
    }
}


return new Plugin(
    new ApiVersionConstraint('DotterbolagetPlugin', '1.*'),
    new DotterbolagetFilter,
    new DotterbolagetFormatter
);
