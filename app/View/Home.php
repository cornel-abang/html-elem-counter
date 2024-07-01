<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML Element Counter</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/styles.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>HTML Element Counter</h1>
            <p class="subtitle">Count HTML elements on any webpage</p>
        </header>

        <main>
            <section class="info-section">
                <h2>How to Use</h2>
                <p>Enter a valid URL (Ex: http://example.com) and name of an HTML element (Ex: p) to count how many times that element appears on the specified webpage.</p>
                <p>Results will display the URL fetched, date & time of response, response time in milliseconds, and count of the specified HTML element.</p>
                <p>General statistics provide insights into the total URLs checked, average fetch time, and total element counts across all requests made.</p>
            </section>

            <section class="form-section">
                <form id="element-counter-form">
                    <label for="url">URL:</label>
                    <input type="text" id="url" name="url" placeholder="Enter URL (e.g., http://example.com)" required>
                    <label for="element">Element:</label>
                    <input type="text" id="element" name="element" placeholder="Enter HTML element tag (e.g., img)" required>
                    <button type="submit" id="count-elements-btn">Count Elements</button>
                    <div id="loading-indicator" class="loading-indicator">Fetching...</div>
                </form>
            </section>

            <section id="results" class="results-section">
                <!-- Results will be displayed here dynamically -->
            </section>
        </main>
    </div>

    <script src="../../assets/js/script.js"></script>
</body>

</html>