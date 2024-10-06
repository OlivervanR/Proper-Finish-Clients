document.addEventListener('DOMContentLoaded', function() {
    const firstSelect = document.getElementById('first-select');
    const thirdSelect = document.getElementById('third-select');
    const selects = document.querySelectorAll('.progress-segment');

    selects.forEach(select => {
        select.addEventListener('change', function() {
            updateBackgroundColor(select);
            updatePreviousSelects();
        });
        // Initialize background color on page load
        updateBackgroundColor(select);
    });

    function updateBackgroundColor(select) {
        const selectedOption = select.options[select.selectedIndex];
        // Remove all color classes
        select.classList.remove('not-started', 'in-progress', 'complete');

        // Add the class based on the selected option
        if (selectedOption.value === '0') {
            select.classList.add('not-started');
        } else if (selectedOption.value === '1') {
            select.classList.add('in-progress');
        } else if (selectedOption.value === '2') {
            select.classList.add('complete');
        }
    }

    function updatePreviousSelects() {
        if (thirdSelect && thirdSelect.value !== '0') {
            firstSelect.value = '2';
            updateBackgroundColor(firstSelect);
        }
    }
});

