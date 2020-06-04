// Speech synthesis
const synth = window.speechSynthesis;

const inputForm = document.querySelector('form');
const inputTxt = document.querySelector('.text');
const voicesList = document.querySelector('select');

let voices = [];

window.onbeforeunload = function() {
    synth.cancel();
};

function populateVoiceList() {
    voices = synth.getVoices();
    const selectedIndex =
        voicesList.selectedIndex < 0 ? 0 : voicesList.selectedIndex;
    voicesList.innerHTML = '';

    // Setting Russian language
    for (i = 0; i < voices.length; i++) {
        if (voices[i].lang === 'ru-RU') {
            const option = document.createElement('option');
            option.textContent = voices[i].name + ' (' + voices[i].lang + ')';

            option.setAttribute('data-lang', voices[i].lang);
            option.setAttribute('data-name', voices[i].name);
            voicesList.appendChild(option);
        }
    }
    voicesList.selectedIndex = selectedIndex;
}

populateVoiceList();
if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = populateVoiceList;
}

function speak(text) {
    if (synth.speaking) {
        console.log('speechSynthesis.speaking');
        synth.cancel();
        setTimeout(speak, 300);
    } else if (text !== '') {
        const utterThis = new SpeechSynthesisUtterance(text);
        // utterThis.onend = function(event) {
        //     console.log('SpeechSynthesisUtterance.onend');
        // };
        utterThis.onerror = function(event) {
            console.error('SpeechSynthesisUtterance.onerror');
        };
        const selectedOption = voicesList.selectedOptions[0].getAttribute(
            'data-name'
        );
        for (i = 0; i < voices.length; i++) {
            if (voices[i].name === selectedOption) {
                utterThis.voice = voices[i];
            }
        }

        utterThis.onpause = function(event) {
            const char = event.utterance.text.charAt(event.charIndex);
            console.log(
                'Speech paused at character ' +
                event.charIndex +
                ' of "' +
                event.utterance.text +
                '", which is "' +
                char +
                '".'
            );
        };

        utterThis.pitch = 1;
        utterThis.rate = 1;

        synth.speak(utterThis);
    }
}

inputForm.onsubmit = function(event) {
    event.preventDefault();

    speak();

    inputTxt.blur();
};