document.addEventListener('DOMContentLoaded', function () {
    const receipts = document.querySelectorAll('.receipt');
    let currentIndex = receipts.length - 1;

    function updatePageIndicator() {
        const pageIndicator = document.getElementById("pageIndicator");
        if (pageIndicator) {
            pageIndicator.textContent = `${currentIndex + 1} / ${receipts.length}`;
        }
    }

    function displayReceipt(index) {
        receipts.forEach((receipt, idx) => {
            receipt.style.display = (idx === index) ? 'block' : 'none';
        });

        const prevButton = document.getElementById('prevReceiptBtn');
        const nextButton = document.getElementById('nextReceiptBtn');

        if (prevButton && nextButton) {
            prevButton.disabled = (index === 0);
            nextButton.disabled = (index === receipts.length - 1);
        }

        updatePageIndicator();
    }

    window.showPreviousReceipt = function () {
        if (currentIndex > 0) {
            currentIndex--;
            displayReceipt(currentIndex);
        }
    };

    window.showNextReceipt = function () {
        if (currentIndex < receipts.length - 1) {
            currentIndex++;
            displayReceipt(currentIndex);
        }
    };

    if (receipts.length > 0) {
        displayReceipt(currentIndex);
    }
});
