<h2><?php echo $name ?></h2>
<table class="table">
    <thead class="thead-inverse">
        <tr>
            <th>Name</th>
            <th>Last price</th>
            <th>Cost</th>
            <th>Value</th>
            <th>3 months</th>
            <th>6 months</th>
            <th>12 months</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($funds as $fund) : ?>
        <tr>
            <td><?php echo $fund->name ?></td>
            <td><?php echo $fund->lastPrice ?> (<?php echo $fund->change ?>)</td>
            <td>£<?php echo number_format($fund->cost,2) ?></td>
            <td>£<?php echo number_format($fund->value,2) ?></td>
            <td><?php echo $fund->get('3months') ?></td>
            <td><?php echo $fund->get('6months') ?></td>
            <td><?php echo $fund->get('12months') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
