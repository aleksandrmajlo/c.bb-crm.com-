$(document).ready(function(){
    $('#phone-number').mask('+000 000 000 0000', {
        placeholder: "+_ ___ ___ ____",
        onKeyPress: function(val, e, field, options) {
            field.mask(getMask(val), options);
        }
    });

    $('#date-picker').datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect: function(dateText) {
            selectedDate = dateText;
            showAvailableTimes();
        }
    });

    function getMask(val) {
        var mask;
        if (val.startsWith('+1')) {
            mask = '+1 (000) 000-0000'; // USA
        } else if (val.startsWith('+49')) {
            mask = '+49 000 0000000'; // Germany
        } else if (val.startsWith('+7')) {
            mask = '+7 (000) 000-00-00'; // Russia and Kazakhstan
        } else if (val.startsWith('+380')) {
            mask = '+380 (00) 000-00-00'; // Ukraine
        } else {
            mask = '+000 000 000 0000'; // Default
        }
        return mask;
    }
});

let phoneNumber = '';
let selectedGame = '';
let selectedDate = '';
let selectedTime = '';

function showCodeField() {
    phoneNumber = document.getElementById('phone-number').value;
    if (phoneNumber) {
        document.getElementById('auth-container').classList.add('hidden');
        document.getElementById('code-container').classList.remove('hidden');
        document.getElementById('code-instruction').innerText = translations[document.documentElement.lang]['enter_code'] + ` ${phoneNumber}`;
    }
}

function showMainMenu() {
    document.getElementById('code-container').classList.add('hidden');
    document.getElementById('main-menu-container').classList.remove('hidden');
}

function showGameSelection() {
    document.getElementById('main-menu-container').classList.add('hidden');
    document.getElementById('game-container').classList.remove('hidden');
}

function selectGame(game) {
    selectedGame = game;
    document.querySelectorAll('.game-option').forEach(option => option.style.backgroundColor = '#f0f8ff');
    document.querySelector(`.game-option[onclick="selectGame('${game}')"]`).style.backgroundColor = '#cceeff';
}

function showSchedule() {
    if (selectedGame) {
        document.getElementById('game-container').classList.add('hidden');
        document.getElementById('schedule-container').classList.remove('hidden');
    }
}

function showAvailableTimes() {
    const scheduleContainer = document.querySelector('.schedule');
    scheduleContainer.innerHTML = '';
    const times = ['10:00', '11:00', '12:00', '13:00'];
    times.forEach(time => {
        const div = document.createElement('div');
        div.className = 'time-slot';
        div.innerText = time;
        div.onclick = () => {
            selectedTime = time;
            document.querySelectorAll('.time-slot').forEach(slot => slot.style.backgroundColor = '#f0f8ff');
            div.style.backgroundColor = '#cceeff';
        };
        scheduleContainer.appendChild(div);
    });
}

function showConfirmation() {
    if (selectedDate && selectedTime) {
        document.getElementById('schedule-container').classList.add('hidden');
        document.getElementById('confirmation-container').classList.remove('hidden');
        document.getElementById('confirmation-phone').innerText = translations[document.documentElement.lang]['phone'] + `: ${phoneNumber}`;
        document.getElementById('confirmation-game').innerText = translations[document.documentElement.lang]['game_type'] + `: ${selectedGame}`;
        document.getElementById('confirmation-date').innerText = translations[document.documentElement.lang]['date'] + `: ${selectedDate}`;
        document.getElementById('confirmation-time').innerText = translations[document.documentElement.lang]['time'] + `: ${selectedTime}`;
    }
}

function showReservations() {
    document.getElementById('main-menu-container').classList.add('hidden');
    document.getElementById('reservations-container').classList.remove('hidden');
}

function cancelReservation() {
    alert('Бронь успешно отменена!');
    // Здесь может быть интеграция с сервером для отмены брони
}

function rescheduleReservation() {
    alert('Бронь успешно перенесена!');
    // Здесь может быть интеграция с сервером для переноса брони
}

function finalizeBooking() {
    alert('Бронь успешно оплачена!');
    // Здесь может быть интеграция с платежной системой
}

function switchLanguage(lang) {
    document.documentElement.lang = lang;
    document.querySelectorAll('[id]').forEach(element => {
        const translationKey = element.id;
        if (translations[lang][translationKey]) {
            element.innerText = translations[lang][translationKey];
        }
    });
    document.getElementById('phone-number').placeholder = translations[lang]['enter_phone'];
    document.getElementById('verification-code').placeholder = translations[lang]['enter_code'];
    document.getElementById('get_code').value = translations[lang]['get_code'];
    document.getElementById('login').value = translations[lang]['login'];
    document.getElementById('next_game').value = translations[lang]['next_game'];
    document.getElementById('next_schedule').value = translations[lang]['next_schedule'];
}

function initializePage() {
    switchLanguage(document.documentElement.lang);
}
