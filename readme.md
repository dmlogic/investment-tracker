# Investment tracker

Originally made to view an overall summary of ISA funds held at [Hargreaves Landsdown](https://hl.co.uk), it can be used for anyone with a suitable [Provider](https://github.com/dmlogic/investment-tracker/blob/master/app/Providers/Factory.php#L10) class. Just implement the `getData` method and return the same values as the HL sample.

## Installation

* Run `composer install`
* Create `groups.csv`, `funds.csv` and `users.csv` in `database/seeds/csv` based on samples provided
* `php artisan migrate:refresh --seed`
* `php artisan server` and you're away

## Currency issues

Any values shown are approximate. Remember to check the actual broker website when making transaction decisions.

Automatic conversion is attempted from anything that isn't GBP, but it won't be as accurate as the fund broker.

The `price_units` field in the `funds` table can be used to adjust fund invetment value calculation where the crawled price is in a different unit.