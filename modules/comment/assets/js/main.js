/*$(document).on('click', '.show-reply', showReply);

function showReply(e) {
    var button = $(e.target);
    var inside = button.data("inside");
    var related = button.data("related");
    var material = button.data("material");
    var model = button.data("model");
    var route = button.data("route");
    var block = button.attr("href");

    $.ajax({
        type: 'GET',
        url: route,
        data: {parent_id: inside, material: material, related: related, model: model},
        success: function success(data) {
            $(block).html(data);
        }
    });
}*/
