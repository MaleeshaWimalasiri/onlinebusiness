/* ---------------------------------------------------------------------
 *  Aurora Jewels - Front-end interactions
 * ------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {

    /* Auto-dismiss flash alerts after 4 seconds. */
    document.querySelectorAll('.alert').forEach(function (alert) {
        if (alert.classList.contains('alert-permanent')) {
            return;
        }
        setTimeout(function () {
            alert.style.transition = 'opacity .5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 4000);
    });

    /* Bootstrap-style client-side form validation. */
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    /* Confirm before destructive actions (delete buttons). */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (event) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    /* Live total update on the cart quantity inputs. */
    document.querySelectorAll('.qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            if (parseInt(input.value, 10) < 1 || isNaN(parseInt(input.value, 10))) {
                input.value = 1;
            }
            input.closest('form').submit();
        });
    });

    /* Star rating picker on the review form. */
    var picker = document.querySelector('.rating-picker');
    if (picker) {
        var hidden = picker.parentElement.querySelector('input[name="rating"]');
        var stars = picker.querySelectorAll('span');
        function paint(value) {
            stars.forEach(function (s, i) {
                s.innerHTML = i < value ? '★' : '☆';
            });
        }
        stars.forEach(function (star, index) {
            star.addEventListener('mouseover', function () { paint(index + 1); });
            star.addEventListener('click', function () {
                hidden.value = index + 1;
                paint(index + 1);
            });
        });
        picker.addEventListener('mouseleave', function () {
            paint(parseInt(hidden.value, 10) || 0);
        });
    }
});
