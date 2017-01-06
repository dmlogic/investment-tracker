;(function() {

processGroup = function(group){
    group.find('.fund-row.pending').each(function() {
        loadFund($(this));
        $(this).removeClass("pending");
    })
}

loadFundFromStorage = function(fund) {
    raw = localStorage.getItem("fund"+fund.data('fund-id'));
    if(!raw) {
        return;
    }
    renderFund(fund,JSON.parse(raw),false);
    fund.find('.stale-date').text("Stale");
    fund.addClass("stale");
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
    valueStr = '£'+formatNumber(data.value)+'<br><span class="profit';
    profit = parseFloat(data.profit);
    if(profit < 1) {
        profit = profit * -1;
        valueStr += ' loss">(-';
    } else {
        valueStr += '">(';
    }
    dateObj = new Date(data.date);
    dateStr = 'Last checked: '+String(data.date).substring(0,10)+' at '+pad(dateObj.getUTCHours())+':'+pad(dateObj.getUTCMinutes());
    valueStr += '£'+formatNumber(profit)+')</span>'
    parent.find('.value').html(valueStr);
    parent.find('.three-months').html(renderPerformance(data.m3));
    parent.find('.six-months').html(renderPerformance(data.m6));
    parent.find('.twelve-months').html(renderPerformance(data.m12));
    parent.find('.last-checked').html(dateStr);

    if(shouldUpdate) {
        updateGroup(parent,data.value,data.profit);
    }
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

function pad(val) {
    return String("00000" + val).slice(-2);
}

// loop each table
    // loop each row (function needed)
    // mark as loading
    // lookup value
    // save result to local storage
    // process local storage for this result (function needed)
    // mark as current

// Then when we load the page we can mark everything as stale, loop every single row and grab from local storage

    $('.fund-row').each(function() {
        loadFundFromStorage($(this));
    })
    $('.fund-group').each(function(){
        processGroup($(this));
    })
})();