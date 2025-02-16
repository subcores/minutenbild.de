
import 'bootstrap/dist/css/bootstrap.min.css'
import './styles/app.css';

import 'bootstrap'
// import 'dropzone'
import { Dropzone } from "dropzone";

/**
 * ------------------------------------------------------------------------
 * Toggle CSS Class {className} at HtmlElement with id {eId}
 * Stores the state at localStorage if {remember} is set to true.
 * ------------------------------------------------------------------------
 */
window.toggleClass = (eId, className, remember = false) => {
    const e = document.getElementById(eId);
    if (null === e) {
        return;
    }
    if (true === remember) {
        localStorage.setItem('toggle/'+eId+'/'+className, localStorage.getItem('toggle/'+eId+'/'+className) === 'on' ? 'off' : 'on')
    }
    e.classList.toggle(className);
}

window.addClass = (element, className) => element.classList.contains(className) ? null : element.classList.add(className);
window.removeClass = (element, className) => element.classList.contains(className) ? element.classList.remove(className) : null;

window.isEqualArray = (a, b) => JSON.stringify(a) === JSON.stringify(b);

/**
 * ------------------------------------------------------------------------
 * Copy text to clipboard
 * ------------------------------------------------------------------------
 */
window.addToClipboard = async (content) => {
    try {
        await navigator.clipboard.writeText(content);
        console.log('Content copied to clipboard');
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
};

/**
 * ------------------------------------------------------------------------
 * Color Themes / switcher
 * ------------------------------------------------------------------------
 */
window.setTheme = (theme) => {
    theme = theme instanceof PointerEvent ? theme.currentTarget : theme;
    let setTo = typeof theme === 'object' ? theme.dataset.themeValue : theme;
    document.documentElement.dataset.bsTheme = setTo === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : setTo;
    localStorage.setItem('theme', setTo);
    document.querySelectorAll('[data-theme-value]').forEach((e) => {
        e.removeEventListener('click', setTheme, true);
        removeClass(e, 'd-none');
        if (e.dataset.themeValue === localStorage.getItem('theme')) {
            addClass(e, 'd-none');
            return;
        }
        e.addEventListener('click', setTheme, true);
    });
};
null === localStorage.getItem('theme') ? window.setTheme('auto') : window.setTheme(localStorage.getItem('theme'));


/**
 * ------------------------------------------------------------------------
 * Dropzone
 * ------------------------------------------------------------------------
 */
// document.querySelectorAll('#dropZone').forEach(el => {
//     const dropArea = el;
// console.log(dropArea);
//     ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
//         dropArea.addEventListener(eventName, preventDefaults, false)
//     });
//
//     ['dragenter', 'dragover'].forEach(eventName => {
//         dropArea.addEventListener(eventName, highlight, false)
//     });
//
//     ['dragleave', 'drop'].forEach(eventName => {
//         dropArea.addEventListener(eventName, unhighlight, false)
//     })
//
//     dropArea.addEventListener('ondrop', (e) => {
//         let file = e.dataTransfer.files[0];
//         console.log(file);
//         let reader = new FileReader()
//         reader.readAsDataURL(file)
//         reader.onloadend = function() {
//             let img = document.createElement('img')
//             img.src = reader.result
//             document.getElementById('dropZone').appendChild(img)
//         }
//     }, false)
//
//     function highlight(e) {
//         addClass(dropArea, 'border-success');
//     }
//
//     function unhighlight(e) {
//         removeClass(dropArea, 'border-success');
//     }
//
//     function preventDefaults (e) {
//         console.log('prevent - ', e.type);
//         e.preventDefault()
//         e.stopPropagation()
//     }
//
//     function previewFile(file) {
//         let reader = new FileReader()
//         reader.readAsDataURL(file)
//         reader.onloadend = function() {
//             let img = document.createElement('img')
//             img.src = reader.result
//             document.getElementById('gallery').appendChild(img)
//         }
//     }
// });

