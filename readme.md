# Investment tracker

Originally made to view an overall summary of ISA funds held at [Hargreaves Landsdown](https://hl.co.uk), it can be used for anyone with a suitable [Provider](https://github.com/dmlogic/investment-tracker/blob/master/app/Providers/Factory.php#L10) class. Just implement the `getData` method and return the same values as the HL sample.

## Installation

* Run `composer install`
* Copy `config/funds.sample.php` to `config/funds.php` and complete your data
* Fire up a webserver from the `public` folder and visit with your browser

## Currency issues

As this was made for HL, most pricing is looked up in UK pence which should have the label 'pence' in your config file. Conversion of investment totals for other currencies should work fine and is done via [fixer.io](http://fixer.io/)
