document.getElementById('element-counter-form').addEventListener('submit', handleFormSubmit);
/**
 * Valid: array of html tag names
 * For: quick client-side validation 
 * (used this approach for the sake of simplicity)
 * 
 * (copied array & might be incomplete)
 */
const VALID_HTML_TAGS = [
    'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio', 'b', 'base', 'bdi', 'bdo', 'blockquote',
    'body', 'br', 'button', 'canvas', 'caption', 'cite', 'code', 'col', 'colgroup', 'data', 'datalist',
    'dd', 'del', 'details', 'dfn', 'dialog', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'figcaption',
    'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html',
    'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link', 'main', 'map', 'mark',
    'meta', 'meter', 'nav', 'noscript', 'object', 'ol', 'optgroup', 'option', 'output', 'p', 'param',
    'picture', 'pre', 'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'script', 'section', 'select',
    'small', 'source', 'span', 'strong', 'style', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'template',
    'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track', 'u', 'ul', 'var', 'video', 'wbr',
    // HTML5 specific tags
    'article', 'aside', 'bdi', 'command', 'details', 'dialog', 'figcaption', 'figure', 'footer', 'header',
    'hgroup', 'mark', 'meter', 'nav', 'progress', 'rp', 'rt', 'ruby', 'section', 'summary', 'time', 'wbr'
];

/**
 * This handles the form submission event
 * 
 * @param {Event} event 
 */
function handleFormSubmit(event) {
    event.preventDefault();

    const url = document.getElementById('url').value.trim();
    const element = document.getElementById('element').value.trim();
    const loadingIndicator = document.getElementById('loading-indicator');

    /**
     * Client-side input validation
     */
    if (!validateUrl(url) || !validateElement(element)) {
        displayError('Invalid input. Please provide a valid URL and HTML element name.');
        return;
    }

    // Show loading indicator
    loadingIndicator.style.display = 'block';

    sendRequest(url, element)
        .then(handleResponse)
        .catch(handleError)
        .finally(() => {
            // Hide loading indicator after request completes
            loadingIndicator.style.display = 'none';
        });
}

/**
 * Using REGEX to validate different parts of the url 
 * (I copied the REGEX)
 * 
 * We need this to always STRICTLY return a boolean
 * So: we use doubl negation ('!!') just to ensure
 * 
 * @param {string} url
 * 
 * @return {boolean}
 */
function validateUrl(url) {
    /**
     * REGEX covers:
     * protocol - (http:// or http://),
     * domain name, port, and path
     */
    const urlPattern = /^(https?:\/\/)?([a-z\d-]+\.)+[a-z]{2,}(:\d+)?(\/[^\s]*)?$/i;
    return !!urlPattern.test(url);
}

/**
 * Simple validation against list of valid HTML tags
 * 
 * @param {string} element
 * 
 * @return {boolean}
 */
function validateElement(element) {
    /**
     * Ensure case-insensitive matching
     */
    const elementLowerCase = element.toLowerCase();
 
    return VALID_HTML_TAGS.includes(elementLowerCase);
}

/**
 * Sends to app entry point - index.php
 * 
 * @param {string} url
 * @param {string} element
 * 
 * @return {Promise<Response>}
 */
function sendRequest(url, element) {
    return fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `url=${encodeURIComponent(url)}&element=${encodeURIComponent(element)}`
    })
    .then(response => response.json());
}

/**
 * Handles the response from backend
 * and updates the DOM accordingly
 * 
 * @param {Object} data
 */
function handleResponse(data) {
    const resultsDiv = document.getElementById('results');

    if (data.error) {
        resultsDiv.innerHTML = `<p class="error">${escapeHtml(data.error)}</p>`;
    } else {
        resultsDiv.innerHTML = `
            <h2>Request Results</h2>
            <p>${escapeHtml(data.request_results)}</p>
            <h2>General Statistics</h2>
            <p>${escapeHtml(data.statistics)}</p>
        `;
    }
}

/**
 * Handles any errors that occur during the fetch request
 * 
 * @param {Error} error
 */
function handleError(error) {
    console.error('Error:', error);
    document.getElementById('results').innerHTML = 
        '<p class="error">An error occurred while processing your request.</p>';
}

/**
 * Escape and clean HTML characters
 * to prevent XSS attacks
 * 
 * @param {string} unsafeString
 * 
 * @return {string} - The escaped string
 */
function escapeHtml(unsafeString) {
    return unsafeString
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;")
        .replace(/\n/g, '<br>');
}

/**
 * Displays error messages to the user
 * 
 * @param {string} message
 */
function displayError(message) {
    document.getElementById('results').innerHTML = `<p class="error">${escapeHtml(message)}</p>`;
}