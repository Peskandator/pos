$(document).ready(function(){
    $(`#deleteCompanyUserConfirm`).click(function (e) {
        $(`.delete-company-user-form`).submit();
    });

    $(`.js-delete-record-button`).click(function (e) {
        let recordId = parseInt($(this).attr('data-record-id'));
        $(`.js-delete-record-id`).val(recordId);

        let recordName = $(this).attr('data-record-name');
        if (!recordName) {
            recordName = recordId;
        }
        $(`.js-modal-record-name`).text(recordName);
    });

    $(`.js-modal-delete-confirm`).click(function (e) {
        $(`.js-delete-form`).submit();
    });
});