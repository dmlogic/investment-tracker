<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Investment peformance</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/app.css" rel="stylesheet">

  </head>

  <body>

    <div class="container-fluid">

        <h1 class="display-4">Investment performance</h1>

        <?php
        foreach($groups as $group) : ?>
            <h2 class="group-heading"><?php echo $group->name ?></h2>
            <div class="group-summary">
                <p>Total group value: <strong class="group-value"></strong></p>
                <p>Total group profit: <strong class="group-profit"></strong></p>
            </div>
            <table class="table table-striped" data-total-funds="<?php echo $group->funds->count()  ?>" data-funds-loaded="0" data-group-value="0" data-group-profit="0">
                <thead class="thead-inverse">
                    <tr>
                        <th>Name</th>
                        <th class="value-heading">Last price</th>
                        <th class="value-heading">Cost</th>
                        <th class="value-heading">Value</th>
                        <th class="value-heading">3 months</th>
                        <th class="value-heading">6 months</th>
                        <th class="value-heading">12 months</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($group->funds as $fund) : ?>
                    <tr class="fund-row loading" data-group-id="<?php echo $group->id ?>" data-fund-id="<?php echo $fund->id ?>">
                        <td class="value-cell fund-name"><a href="<?php echo $fund->url ?>" target="_blank"><?php echo $fund->name ?></a></td>
                        <td class="value-cell last-price"><small>loading...</small></td>
                        <td class="value-cell cost">£<?php echo number_format($fund->cost_price,2) ?></td>
                        <td class="value-cell value"></td>
                        <td class="value-cell three-months"></td>
                        <td class="value-cell six-months"></td>
                        <td class="value-cell twelve-months"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="/js/jquery.tablesorter.js"></script>
    <script src="/js/app.js"></script>
  </body>
</html>
