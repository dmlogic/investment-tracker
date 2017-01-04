loadFund = function(parent) {
    url = '/?action=dragon/fund/data&group_id='+parent.data('group-id')+'&fund_id='+parent.data('fund-id')
    $.ajax({
        url: url,
        context: parent,
        success: function(resp) {
            renderFund(parent,resp);
        },
        dataType: "json",
        async: false
    }).done(function() {
        $( this ).removeClass( "loading" );
    });
}

formatNumber = function(n) {
    n = parseFloat(n);
    return n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
}

renderFund = function(parent,data) {
    parent.addClass(data.last_direction);

    changeStr = '<strong>'+formatNumber(data.last_price)+'</strong>';
    if(data.last_direction) {
        changeStr += ' ('+data.last_change+'%)';
    }
    parent.find('.last-price').html(changeStr);
    valueStr = '£'+formatNumber(data.value)+'<br><span class="profit';
    profit = parseFloat(data.profit);
    if(profit < 1) {
        profit = profit * -1;
        valueStr += ' loss">(-';
    } else {
        valueStr += '">(';
    }
    valueStr += '£'+formatNumber(profit)+')</span>'
    parent.find('.value').html(valueStr);
    parent.find('.three-months').html(renderPerformance(data.m3));
    parent.find('.six-months').html(renderPerformance(data.m6));
    parent.find('.twelve-months').html(renderPerformance(data.m12));

    updateGroup(parent,data.value,data.profit);
}

updateGroup = function(fundRow,fundValue,fundProfit) {
    groupTable = fundRow.closest('.table');

    loaded = parseInt(groupTable.data('funds-loaded'));
    value = parseFloat(groupTable.data('group-value'));
    profit = parseFloat(groupTable.data('group-profit'));

    groupTable.data('funds-loaded',loaded + 1)
    groupTable.data('group-value', value + fundValue);
    groupTable.data('group-profit', profit + fundProfit);

    if(groupTable.data('funds-loaded') < groupTable.data('total-funds')) {
        return;
    }

    groupTable.tablesorter();

    groupValue = '£'+formatNumber(groupTable.data('group-value'));
    groupProfit = groupTable.data('group-profit');
    profitStr = '<span class="';
    if(groupProfit < 0) {
        groupProfit = groupProfit * -1;
        profitStr += 'performance-down">-';
    } else {
        profitStr += 'performance-up">';
    }
    profitStr += '£'+formatNumber(groupProfit)+'</span>';

    groupTable = fundRow.closest('.table');
    summary = groupTable.prev('div.group-summary')


    summary.find('.group-value').html(groupValue);
    summary.find('.group-profit').html(profitStr);
    summary.slideDown();
}

renderPerformance = function(value) {
    if(typeof(value) == "undefined" || value > 1000) {
        return '-';
    }

    value = parseFloat(value);
    if(value < 0) {
        cls = 'performance-down';
    } else {
        cls = 'performance-up'
    }

    str = '<span class="'+cls+'">'+formatNumber(value)+'%</span>';
    return str;
}

$('.fund-row').each(function(){
    loadFund($(this));
})