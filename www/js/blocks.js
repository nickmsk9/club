// Persistence for block state using localStorage
function setBlockState(id, state) {
    if (window.localStorage) {
        localStorage.setItem('block_' + id, state);
    }
}
function getBlockState(id) {
    return window.localStorage ? localStorage.getItem('block_' + id) : null;
}

function show_hide_no_img(id)
{
    var klappText = document.getElementById('s' + id);
    var klappBild = document.getElementById('pic' + id);

    if (klappText.style.display == 'none') {
        klappText.style.display = 'block';
        // klappBild.src = 'images/blank.gif';
    }
    else {
        klappText.style.display = 'none';
        // klappBild.src = 'images/blank.gif';
    }
}

function show_hide(id)
{
    var klappText = document.getElementById('s' + id);
    var klappBild = document.getElementById('pic' + id);

    if (klappText.style.display == 'none') {
        klappText.style.display = 'block';
        klappBild.src = 'pic/minus.gif';
        klappBild.title = 'Свернуть';
        setBlockState(id, 'expanded');
    } else {
        klappText.style.display = 'none';
        klappBild.src = 'pic/plus.gif';
        klappBild.title = 'Развернуть';
        setBlockState(id, 'collapsed');
    }
}

// Apply saved states on page load
document.addEventListener('DOMContentLoaded', function() {
    // Define your block IDs here
    var blockIds = [1, 2, 3]; // TODO: replace with actual IDs or generate dynamically
    blockIds.forEach(function(id) {
        var state = getBlockState(id);
        var klappText = document.getElementById('s' + id);
        var klappBild = document.getElementById('pic' + id);
        if (state === 'collapsed') {
            klappText.style.display = 'none';
            klappBild.src = 'pic/plus.gif';
            klappBild.title = 'Развернуть';
        } else if (state === 'expanded') {
            klappText.style.display = 'block';
            klappBild.src = 'pic/minus.gif';
            klappBild.title = 'Свернуть';
        }
    });
});