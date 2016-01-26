loadFund = function(parent) {
    url = '/fund/'+parent.data('group-id')+'/'+parent.data('fund-id')
    $.ajax({
        url: url,
        context: parent,
        success: function(resp) {
            renderFund(parent,resp)
        },
        dataType: "json"
    }).done(function() {
        $( this ).removeClass( "loading" );
    });
}

formatNumber = function(n) {
    n = parseFloat(n);
    return n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
}

renderFund = function(parent,data) {
    parent.addClass(data.lastDirection);

    changeStr = '<strong>'+formatNumber(data.lastPrice)+'</strong>';
    if(data.lastDirection) {
        changeStr += ' ('+data.lastChange+'%)';
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
}

renderPerformance = function(value) {
    if(typeof(value) == "undefined") {
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