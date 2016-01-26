<?php $this->layout('page') ?>

<?php
foreach($groups as $groupId => $group) : ?>
    <h2 class="group-heading"><?php echo $group['group_name'] ?></h2>
    <div class="group-summary">
        <p>Total group value: <strong class="group-value"></strong></p>
        <p>Total group profit: <strong class="group-profit"></strong></p>
    </div>
    <table class="table table-striped" data-total-funds="<?php echo count($group['funds']) ?>" data-funds-loaded="0" data-group-value="0" data-group-profit="0">
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
        <?php foreach($group['funds'] as $fundId => $fund) : ?>
            <tr class="fund-row loading" data-group-id="<?php echo $groupId ?>" data-fund-id="<?php echo $fundId ?>">
                <td class="value-cell fund-name"><a href="<?php echo $fund['url'] ?>" target="_blank"><?php echo $fund['name'] ?></a></td>
                <td class="value-cell last-price"><small>loading...</small></td>
                <td class="value-cell cost">£<?php echo number_format($fund['cost'],2) ?></td>
                <td class="value-cell value"></td>
                <td class="value-cell three-months"></td>
                <td class="value-cell six-months"></td>
                <td class="value-cell twelve-months"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>