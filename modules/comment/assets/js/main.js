$(document).on('click', '.replyBtnInside', replyBtn);

function replyBtn(e) {
    e.preventDefault();

    var button = $(e.target);
    var id = button.data("id");
    var parent_id = button.data("parent_id");

    var user = $('#comment' + id).clone().find('.username').children().remove().end().text();
    var form = $('#replyForm' + parent_id).clone();
    $('#replyBtnInside_' + parent_id + '_' + id).html('');

    form.find('textarea').val('@' + user + ' ');
    form.appendTo('#replyBtnInside_' + parent_id + '_' + id);
}