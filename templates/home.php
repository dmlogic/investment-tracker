<?php $this->layout('page') ?>

<?php foreach($groups as $groupId => $group) : ?>
    <h2><?php echo $group['group_name'] ?></h2>
    <table class="table table-striped">
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
        <?php foreach($group['funds'] as $fundId => $fund) : ?>
            <tr class="fund-row loading" data-group-id="<?php echo $groupId ?>" data-fund-id="<?php echo $fundId ?>">
                <td><a href="<?php echo $fund['url'] ?>" target="_blank"><?php echo $fund['name'] ?></a></td>
                <td class="last-price"><small>loading...</small></td>
                <td class="cost">£<?php echo number_format($fund['cost'],2) ?></td>
                <td class="value"></td>
                <td class="three-months"></td>
                <td class="six-months"></td>
                <td class="twelve-months"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>