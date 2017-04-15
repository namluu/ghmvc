$(function() {
    console.log('abc');
    $('#colorselector').colorselector();
});

function confirmDelete() {
    if (confirm("Delete this item?")) {
        return true;
    } else {
        return false;
    }
}