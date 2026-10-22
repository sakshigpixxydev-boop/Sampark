import './bootstrap';
import './validation';
import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';

window.Alpine = Alpine;
window.TomSelect = TomSelect;
window.flatpickr = flatpickr;

// Global date formatting function - DD/MM/YYYY
window.formatDate = function(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

const initTomSelect = (container = document) => {
    container.querySelectorAll('select.searchable-select:not(.ts-hidden-visited)').forEach((el) => {
        if (el.classList.contains('tomselected')) return;

        let settings = {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            plugins: ['dropdown_input', 'remove_button']
        };

        if (el.dataset.preserveOrder === 'true') {
            settings.sortField = {
                field: "$order"
            };
        }

        try {
            el.tomselect = new TomSelect(el, settings);
        } catch (e) {
            console.error('TomSelect init failed', e, el);
        }
    });
};

const initFlatpickr = (container = document) => {
    // Apply Flatpickr to all date inputs with DD/MM/YYYY format
    const dateInputs = container.querySelectorAll('input[type="text"].date-picker, input[type="date"]:not(.flatpickr-input)');
    
    dateInputs.forEach((el) => {
        if (el.classList.contains('flatpickr-input')) return;

        try {
            const config = {
                dateFormat: 'd/m/Y',
                allowInput: true,
                onChange: (selectedDates, dateStr, instance) => {
                    // Update the input value and trigger change event for Alpine.js
                    el.value = dateStr;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            // Add placeholder if not present
            if (!el.placeholder) {
                el.placeholder = 'DD/MM/YYYY';
            }

            // Add age validation limits only for date of birth field
            if (el.id === 'dob' || el.id === 'birthdate') {
                const maxDate = new Date();
                maxDate.setFullYear(maxDate.getFullYear() - 10);

                const minDate = new Date();
                minDate.setFullYear(minDate.getFullYear() - 18);

                config.maxDate = maxDate;
                config.minDate = minDate;
            }

            flatpickr(el, config);
        } catch (e) {
            console.error('Flatpickr init failed', e, el);
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initTomSelect();
    initFlatpickr();

    // Watch for new elements added to DOM (e.g. by Alpine or other scripts)
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) {
                initTomSelect();
                initFlatpickr();
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});

Alpine.start();
