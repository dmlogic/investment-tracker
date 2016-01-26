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

    changeStr = '£'+formatNumber(data.lastPrice);
    if(data.lastDirection) {
        changeStr += ' ('+data.lastChange+')';
    }
    parent.find('.last-price').html(changeStr);
    valueStr = '£'+formatNumber(data.value)+'<br>(<span class="profit';
    profit = parseFloat(data.profit);
    if(profit < 1) {
        profit = profit * -1;
        valueStr += ' loss">-';
    } else {
        valueStr += '">';
    }
    valueStr += '£'+formatNumber(profit)+'</span>)'
    console.log('profit: '+profit);
    parent.find('.value').html(valueStr);
    if(typeof(data.m3) != "undefined") {
        m3 = formatNumber(data.m3)+'%';
        m6 = formatNumber(data.m6)+'%';
        m12 = formatNumber(data.m12)+'%';
    } else {
        m3 = m6 = m12 = '-';
    }
    parent.find('.three-months').html(m3);
    parent.find('.six-months').html(m6);
    parent.find('.twelve-months').html(m12);
}

$('.fund-row').each(function(){
    loadFund($(this));
})