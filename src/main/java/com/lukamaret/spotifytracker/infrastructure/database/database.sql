CREATE TABLE Artist
(
    id   INTEGER PRIMARY KEY AUTO_INCREMENT,
    url  VARCHAR(255) NOT NULL,
    uri  VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE Playlist
(
    id   INTEGER PRIMARY KEY AUTO_INCREMENT,
    url  VARCHAR(255) NOT NULL,
    uri  VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE Track
(
    id   INTEGER PRIMARY KEY AUTO_INCREMENT,
    url  VARCHAR(255) NOT NULL,
    uri  VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE Author
(
    id_track  INTEGER NOT NULL,
    id_artist INTEGER NOT NULL,
    PRIMARY KEY (id_track, id_artist),
    FOREIGN KEY (id_track) REFERENCES Track (id),
    FOREIGN KEY (id_artist) REFERENCES Artist (id)
);

CREATE TABLE Listening
(
    date        TIMESTAMP PRIMARY KEY,
    id_track    INTEGER NOT NULL,
    id_playlist INTEGER NOT NULL,
    FOREIGN KEY (id_track) REFERENCES Track (id),
    FOREIGN KEY (id_playlist) REFERENCES Playlist (id)
);
