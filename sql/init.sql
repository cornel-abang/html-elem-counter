CREATE TABLE domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    name VARCHAR(255) UNIQUE NOT NULL,
    FOREIGN KEY (domain_id) REFERENCES domains(id)
);

CREATE TABLE elements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    url_id INT NOT NULL,
    element_id INT NOT NULL,
    request_time DATETIME NOT NULL,
    response_time INT DEFAULT 0,
    element_count INT DEFAULT 0,
    FOREIGN KEY (domain_id) REFERENCES domains(id),
    FOREIGN KEY (url_id) REFERENCES urls(id),
    FOREIGN KEY (element_id) REFERENCES elements(id),
    -- I noticed that they are frequently used
    INDEX (domain_id),
    INDEX (url_id),
    INDEX (element_id),
    INDEX (request_time)
);