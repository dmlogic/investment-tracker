;(function() {

processGroup = function(group){
    group.find('.fund-row.pending').each(function() {
        loadFund($(this));
        $(this).removeClass("pending");
    })
}

loadFundFromAttributes = function(fund) {
    value  = parseFloat(fund.data('fund-value'));
    if(!value) {
        fund.addClass("error")
        return;
    }
    cost   = parseFloat(fund.data('fund-cost'));
    setLastChecked(fund.data('fund-checked'),fund);
    profit = value - cost;
    setProfitLoss(fund.data('fund-value'),profit,fund);
    fund.find('.stale-date').text("Stale");
    fund.addClass("stale");
    updateGroup(fund,value,profit);
}

loadFund = function(parent) {
    parent.removeClass("stale").addClass("loading");
    url = '/?action=dragon/fund/data&group_id='+parent.data('group-id')+'&fund_id='+parent.data('fund-id')
    $.ajax({
        url: url,
        context: parent,
        success: function(resp) {
            resp.date = new Date();
            renderFund(parent,resp,true);
            localStorage.setItem("fund"+parent.data('fund-id'), JSON.stringify(resp));
        },
        error:function(resp) {
            parent.addClass("error").removeClass("loading");
        },
        dataType: "json",
        async: false
    }).done(function() {
        parent.removeClass( "loading" );
    });
}

formatNumber = function(n) {
    n = parseFloat(n);
    return n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
}

renderFund = function(parent,data,shouldUpdate) {
    parent.addClass(data.last_direction);

    changeStr = '<strong>'+formatNumber(data.last_price)+'</strong>';
    if(data.last_direction) {
        changeStr += ' ('+data.last_change+'%)';
    }
    parent.find('.last-price').html(changeStr);
    setLastChecked(data.date,parent);
    setProfitLoss(data.value,parseFloat(data.profit),parent);

    parent.find('.three-months').html(renderPerformance(data.m3));
    parent.find('.six-months').html(renderPerformance(data.m6));
    parent.find('.twelve-months').html(renderPerformance(data.m12));

    if(shouldUpdate) {
        updateGroup(parent,data.value,data.profit);
    }
}

setLastChecked = function(date,parent) {
    dateObj = new Date(date);
    dateStr = 'Last checked: '+String(date).substring(0,10)+' at '+pad(dateObj.getUTCHours())+':'+pad(dateObj.getUTCMinutes());
    parent.find('.last-checked').html(dateStr);
}

setProfitLoss = function(value,profit,parent) {
    valueStr = '£'+formatNumber(value)+'<br><span class="profit';
    if(profit < 1) {
        profit = profit * -1;
        valueStr += ' loss">(-';
    } else {
        valueStr += '">(';
    }
    valueStr += '£'+formatNumber(profit)+')</span>'
    parent.find('.value').html(valueStr);
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

resetGroup = function(group) {
    groupTable = group.find('.table');

    groupTable.data('funds-loaded',0);
    groupTable.data('group-value',0);
    groupTable.data('group-profit',0);
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

pad = function(val) {
    return String("00000" + val).slice(-2);
}


    $('.fund-row').each(function() {
        loadFundFromAttributes($(this));
    })
    $('.fund-group').each(function(){
        resetGroup($(this));
    })
    $('.refresh').on("click",function(){
        group = $(this).closest('.fund-group');
        $(this).remove();
        processGroup(group);
    })
})();