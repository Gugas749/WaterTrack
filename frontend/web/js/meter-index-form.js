function updateStatusBadge() {
    const value = $('#meter-status-dropdown').val();
    const badge = $('#meter-status-badge');
    const statusMapping = {
        10: {text: 'ATIVO', class: 'bg-success'},
        9:  {text: 'COM PROBLEMAS', class: 'bg-warning'},
        0:  {text: 'DESATIVADO', class: 'bg-danger'}
    };
    const status = statusMapping[value] || {text: 'DESCONHECIDO', class: 'bg-secondary'};
    badge.text(status.text);
    badge.removeClass('bg-success bg-warning bg-danger bg-secondary').addClass(status.class);
}

$(document).ready(function() {
    updateStatusBadge();

    $('#meter-status-dropdown').on('change', updateStatusBadge);
});