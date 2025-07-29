<?php declare(strict_types=1);

namespace SepaQr;

use InvalidArgumentException;
use LogicException;

class SepaQrData
{
    const UTF_8 = 1;
    const ISO8859_1 = 2;
    const ISO8859_2 = 3;
    const ISO8859_4 = 4;
    const ISO8859_5 = 5;
    const ISO8859_7 = 6;
    const ISO8859_10 = 7;
    const ISO8859_15 = 8;

    private $valt = ['ALL','AFN','ARS','AWG','AUD','AZN','BSD','BBD','BDT','BYR','BZD','BMD','BOB','BAM','BWP','BGN','BRL',
                     'BND','KHR','CAD','KYD','CLP','CNY','COP','CRC','HRK','CUP','CZK','DKK','DOP','XCD','EGP','SVC','EEK',
                     'EUR','FKP','FJD','GHC','GIP','GTQ','GGP','GYD','HNL','HKD','HUF','ISK','INR','IDR','IRR','IMP','ILS',
                     'JMD','JPY','JEP','KZT','KPW','KRW','KGS','LAK','LVL','LBP','LRD','LTL','MKD','MYR','MUR','MXN','MNT',
                     'MZN','NAD','NPR','ANG','NZD','NIO','NGN','NOK','OMR','PKR','PAB','PYG','PEN','PHP','PLN','QAR','RON',
                     'RUB','SHP','SAR','RSD','SCR','SGD','SBD','SOS','ZAR','LKR','SEK','CHF','SRD','SYP','TWD','THB','TTD',
                     'TRY','TRL','TVD','UAH','GBP','USD','UYU','UZS','VEF','VND','YER','ZWD'];
    
    /**
     * @var array<string, int|float|string>
     */
    private $sepaValues = array(
        'serviceTag' => 'BCD',
        'version' => 2,
        'characterSet' => 1,
        'identification' => 'SCT'
    );

    protected function formatMoney(float $value = 0): string
    {
        /** @var string */
        $currency = $this->sepaValues['currency'] ?? 'EUR';

        return sprintf(
            '%s%s',
            strtoupper($currency),
            $value > 0 ? number_format($value, 2, '.', '') : ''
        );
    }

    public static function build(): Data
    {
        return new self();
    }
    
    public function setServiceTag(string $serviceTag = 'BCD'): static
    {
        if ($serviceTag !== 'BCD') {
            throw new InvalidArgumentException("Invalid service tag: $serviceTag. Should be BCD.");
        }

        $this->sepaValues['serviceTag'] = $serviceTag;

        return $this;
    }

    public function setVersion(int $version = 2): static
    {
        if ($version < 1 || $version > 2) {
            throw new InvalidArgumentException("Invalid version: $version. Should be either 1 or 2.");
        }

        $this->sepaValues['version'] = $version;

        return $this;
    }

    public function setCharacterSet(int $characterSet = self::UTF_8): static
    {
        if ($characterSet < 1 || $characterSet > 8) {
            throw new InvalidArgumentException("Invalid character set: $characterSet. Should be between 1 and 8.");
        }

        $this->sepaValues['characterSet'] = $characterSet;

        return $this;
    }

    public function setIdentification(string $identification = 'SCT'): static
    {
        if ($identification !== 'SCT') {
            throw new InvalidArgumentException("Invalid identification code: $identification. Should be SCT.");
        }

        $this->sepaValues['identification'] = $identification;

        return $this;
    }

    public function setBic(string $bic): static
    {
        if (strlen($bic) > 0) {
        
            if (strlen($bic) !== 8 && strlen($bic) !== 11) {
                throw new InvalidArgumentException("Invalid BIC of the beneficiary: $bic. Should be either 8 or 11 characters.");
            }
        }            

        $this->sepaValues['bic'] = $bic;

        return $this;
    }

    public function setName(string $name): static
    {
        if (strlen($name) > 70) {
            throw new InvalidArgumentException("Invalid name of the beneficiary: $name. Should be maximum 70 characters.");
        }

        $this->sepaValues['name'] = $name;

        return $this;
    }

    public function setIban(string $iban): static
    {
        if (strlen($iban) > 34) {
            throw new InvalidArgumentException("Invalid account number of the beneficiary: $iban. Should be maximum 34 characters.");
        }

        $this->sepaValues['iban'] = $iban;

        return $this;
    }

    public function setCurrency(string $currency): static
    {
        if (strlen($currency) > 0) {
        
            if (strlen($currency) !== 3) {
                throw new InvalidArgumentException("Invalid currency: $currency. Should be a valid 3 character ISO 4217 code.");
            }

            if (!in_array($currency, $this->valt)) {
                throw new InvalidArgumentException('The currency is not supported or no valid ISO 4217 code');
            }

            $this->sepaValues['currency'] = $currency;

            return $this;
        }

        $this->sepaValues['currency'] = 'EUR';

        return $this;        
    }

    public function setAmount(float $amount): static
    {
        //$amount = floatval($amount);

        if (floatval($amount) > 0.00) {

            //if (($ramount = preg_replace("/^[0-9]+(\.[0-9]{0,5}|\,[0-9]{0,5})?$/", '', $amount) !== '')) {
            if (($ramount = preg_replace("/^[0-9]+(\.[0-9]{0,5})?$/", '', strval($amount)) !== '')) {
                throw new InvalidArgumentException('Amount of the credit transfer must be type of float');
            }
        
            if ($amount < 0.01) {
                throw new InvalidArgumentException("Invalid amount: $amount. Should be minumum 0.01 Euro.");
            }

            if ($amount > 999999999.99) {
                throw new InvalidArgumentException("Invalid amount: $amount. Should be maximum 999999999.99 Euro.");
            }

            $this->sepaValues['amount'] = number_format($amount, 2, '.', '');
            //$this->sepaValues['amount'] = $amount;

            return $this;
        }
        
        $this->sepaValues['amount'] = '';

        return $this;
    }

    public function setPurpose(string $purpose): static
    {
        if (strlen($purpose) > 0) {
        
            if (strlen($purpose) !== 4) {
                throw new InvalidArgumentException("Invalid purpose code: $purpose. Should be 4 characters.");
            }

            if (($rpose = preg_replace("/[A-Z]{4}/", '', $purpose)) !== '') {
                throw new InvalidArgumentException('Only 4 capital letters (A-Z) are allowed');
            }
            
            $this->sepaValues['purpose'] = $purpose;

            return $this;
        }

        $this->sepaValues['purpose'] = '';

        return $this;
    }

    public function setRemittanceReference(string $remittanceReference): static
    {
        if (strlen($remittanceReference) > 0) {
        
            if (strlen($remittanceReference) > 35) {
                throw new InvalidArgumentException("Invalid structured remittance information: $remittanceReference. Should be maximum 35 characters.");
            }

            if (isset($this->sepaValues['remittanceText']) AND strlen($this->sepaValues['remittanceText']) > 0) {
                throw new InvalidArgumentException("Invalid remittance information. Use either structured or unstructured remittance information, not both.");
            }

            if (($rReference = preg_replace("/[0-9a-zA-Z ':,.?-\\\+\\\(\\\)\\/]/", '', $remittanceReference)) !== '') {
                throw new InvalidArgumentException('Only capital letters (A-Za-z), Numbers (0-9) and special characters (/?:\'().,+-space) are allowed');
            }
            
            $this->sepaValues['remittanceReference'] = (string)$remittanceReference;

            return $this;
        }

        $this->sepaValues['remittanceReference'] = '';

        return $this;
    }

    public function setRemittanceText(string $remittanceText): static
    {
        if (strlen($remittanceText) > 0) {
        
            if (strlen($remittanceText) > 140) {
                throw new InvalidArgumentException("Invalid unstructured remittance information: $remittanceText. Should be maximum 140 characters.");
            }

            if (isset($this->sepaValues['remittanceReference']) AND strlen($this->sepaValues['remittanceReference']) > 0) {
                throw new InvalidArgumentException("Invalid remittance information. Use either structured or unstructured remittance information, not both.");
            }

            $this->sepaValues['remittanceText'] = $remittanceText;

            return $this;
        }

        $this->sepaValues['remittanceText'] = '';

        return $this;
    }

    public function setInformation(string $information): static
    {
        if (strlen($information) > 0) {
        
            if (strlen($information) > 70) {
                throw new InvalidArgumentException("Invalid beneficiary to originator information: $information. Should be maximum 70 characters.");
            }

            $this->sepaValues['information'] = $information;

            return $this;
        }

        $this->sepaValues['information'] = '';

        return $this;       
    }

    public function __toString(): string
    {
        $defaults = array(
            'bic' => '',
            'name' => '',
            'iban' => '',
            'currency' => 'EUR',
            'amount' => 0,
            'purpose' => '',
            'remittanceReference' => '',
            'remittanceText' => '',
            'information' => ''
        );

        $values = array_merge($defaults, $this->sepaValues);

        if (!$values['name']) {
            throw new LogicException('Name of the beneficiary is required.');
        }

        if (!$values['iban']) {
            throw new LogicException('Account number of the beneficiary is required.');
        }

        if ($values['version'] === 1 && !$values['bic']) {
            throw new LogicException("BIC of the beneficiary is required.");
        }

        /** @var float */
        $amount = $values['amount'];

        return rtrim(implode("\n", array(
            $values['serviceTag'],
            sprintf('%03d', $values['version']),
            $values['characterSet'],
            $values['identification'],
            $values['bic'],
            $values['name'],
            $values['iban'],
            self::formatMoney($amount),
            //self::formatMoney((string)$values['currency'], (float)$values['amount']),
            $values['purpose'],
            $values['remittanceReference'],
            $values['remittanceText'],
            $values['information']
        )), "\n");
    }
}
