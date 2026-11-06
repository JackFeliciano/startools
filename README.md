# STARTOOLS

STARTOOLS is a startup model written in plain PHP 8.2 with no dependencies. It models what a company must show to reach a one billion dollar exit.

The interface moves from the whole model into its parts. Six tiers trace the path from bootstrap and validation through Seed, Series A, Series B and Series C to exit.

The founder can change assumptions across four connected tabs. Three scenarios compare customer growth. The model tracks money in whole cents, applies churn to existing customers and compounds the worst case month by month.

The round calculator takes pre-money value, investment and stake sold. It returns post-money value, dilution and new ownership, and rejects rounds with invalid results. The model also lists investor questions and the gaps a company still needs to address.


A modelling tool for startups. It takes a founder from the first idea to the
shape an investor expects to read.

## What it does

A founder fills in six figures and gets a month by month projection. Every
figure in the output comes from those six, so a reader can challenge a number
by name instead of arguing with the result.

The assumptions are the price per customer, the monthly churn, the cost to
serve one customer, the new customers each month, the fixed monthly cost, and
the number of months. The model runs the customers forward, applies churn to
the customers who were already there, and adds the new ones after it, so
nobody counted as leaving in the month he arrived.

From that run it reports the customer count, revenue, gross margin, break even
month, payback month, total profit and the deepest cash position. The last one
is the amount a round has to cover.

## The shape an investor reads

Three scenarios come out of one set of assumptions. The slow and the fast ones
move the customer count and leave every other figure alone, so the reader sees
which assumption carries the outcome.

The tool knows four stages, idea, prototype, revenue and growth. Each one
carries the three questions a partner asks before opening the deck, so a
founder knows what he has to answer before he fills in a number.

A readiness check names what is missing. It does not score the founder, because
a score tells him nothing he can act on. A model that ends with no customers,
or never reaches a profitable month, or never brings the cash back to zero
inside the run, says so in plain words.

A round takes an amount and a pre money valuation, and returns the post money,
the share the investor holds and the founder's remaining share. Runway divides
the raise by the average monthly burn.

## Running it

    php tests/run.php      the suite, 36 checks, no dependencies
    php -S 127.0.0.1:8097 -t showroom showroom/index.php

Then open `http://127.0.0.1:8097/`. The prefix lives in STARTOOLS_BASE, which
defaults to `/`, and the front controller strips it before matching a route.

Routes: `/` the model, `/brief` the same model as plain text, `/health`.

## Layout

    src/Model/    Money, Assumptions, Projection, Forecast, Round, Stage,
                  Readiness, Scenario, Book, Brief, Sample
    src/App/      Router, Views, Front
    showroom/     the front controller and the stylesheet
    tests/        the runner, the assertions and five test files
    deploy/       the systemd unit and the nginx location

## Money

Every amount is an integer of cents. The float appears once, when a founder
writes a price, and the arithmetic stays integer after that, so a projection
never drifts by a rounding error across twenty four months.

## Licence

Mozilla Public License 2.0. See LICENSE.
