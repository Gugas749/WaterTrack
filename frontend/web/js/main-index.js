document.addEventListener('DOMContentLoaded', () => {
    const openRightBtn = document.querySelector('[data-toggle="right-panel"]');
    const rightPanel = document.getElementById('rightPanel');
    const closeRightBtn = document.getElementById('closeRightPanel');
    const overlay = document.getElementById('overlay');
    const form = document.getElementById('add-user-form');

    function openRightPanel() {
        rightPanel.style.display = 'block';
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden'; //lock scroll
        requestAnimationFrame(() => rightPanel.classList.add('show'));
    }

    function closeRightPanel() {
        rightPanel.classList.remove('show');
        overlay.style.display = 'none';
        setTimeout(() => {
            rightPanel.style.display = 'none';
            if (form) form.reset();
        }, 300);
    }

    if (openRightBtn) openRightBtn.addEventListener('click', openRightPanel);
    if (closeRightBtn) closeRightBtn.addEventListener('click', closeRightPanel);
    if (overlay) overlay.addEventListener('click', closeRightPanel);

    //----------------------------------------------------
    //----------------------------------------------------
    //----------------------------------------------------
    const detailPanel = document.getElementById('detailPanel');
    const closeDetailBtns = document.querySelectorAll('.closeDetailPanel');

    if (detailPanel && detailPanel.classList.contains('show')) {
        document.body.style.overflow = 'hidden';
        if (window.innerWidth < 768) {
            detailPanel.style.overflowY = 'auto';
        } else {
            detailPanel.style.overflowY = '';
        }
    }

    function closeDetailPanel() {
        detailPanel.classList.remove('show');
        document.body.style.overflow = '';
        const currentUrl = new URL(window.location.href);

        setTimeout(() => {
            detailPanel.style.display = 'none';
            overlay.style.display = 'none';

            //limpar url
            if (currentUrl.searchParams.has('id')) {
                currentUrl.searchParams.delete('id');
                window.location.href = currentUrl.toString();
            }
        }, 300);


    }

    closeDetailBtns.forEach(btn => {
        btn.addEventListener('click', closeDetailPanel);
    });
    if (overlay) overlay.addEventListener('click', closeDetailPanel);
});