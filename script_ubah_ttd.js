document.addEventListener('DOMContentLoaded', function() {
    const editSignatureBtn = document.getElementById('edit-signature-btn');
    const editSignatureContainer = document.getElementById('edit-signature-container');
    const cancelEditSignatureBtn = document.getElementById('cancel-edit-signature');
    const editSignaturePad = document.getElementById('edit-signature-pad');
    const clearNewSignatureBtn = document.getElementById('clear-new-signature');
    const editSignatureDataInput = document.getElementById('edit-signature-data');
    const editSignatureForm = document.getElementById('edit-signature-form');
    if (!editSignatureBtn || !editSignatureContainer || !editSignaturePad || !clearNewSignatureBtn || !editSignatureDataInput || !editSignatureForm) return;
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    const ctx = editSignaturePad.getContext('2d');
    function startDrawing(e) {
        isDrawing = true;
        [lastX, lastY] = [e.offsetX, e.offsetY];
    }
    function draw(e) {
        if (!isDrawing) return;
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        [lastX, lastY] = [e.offsetX, e.offsetY];
    }
    function stopDrawing() {
        isDrawing = false;
    }
    editSignaturePad.addEventListener('mousedown', startDrawing);
    editSignaturePad.addEventListener('mousemove', draw);
    editSignaturePad.addEventListener('mouseup', stopDrawing);
    editSignaturePad.addEventListener('mouseout', stopDrawing);
    clearNewSignatureBtn.addEventListener('click', function() {
        ctx.clearRect(0, 0, editSignaturePad.width, editSignaturePad.height);
    });
    editSignatureBtn.addEventListener('click', function() {
        editSignatureContainer.style.display = 'block';
    });
    cancelEditSignatureBtn.addEventListener('click', function() {
        editSignatureContainer.style.display = 'none';
    });
    editSignatureForm.addEventListener('submit', function(e) {
        const dataURL = editSignaturePad.toDataURL();
        editSignatureDataInput.value = dataURL;
    });
});