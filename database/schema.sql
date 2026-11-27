DROP TABLE IF EXISTS decisions;

CREATE TABLE decisions (
    id              SERIAL       PRIMARY KEY,
    title           VARCHAR(100) NOT NULL,
    cause_of_death  VARCHAR(255) NOT NULL,
    category        VARCHAR(20)  NOT NULL DEFAULT 'rip',
    born_on         DATE         NOT NULL DEFAULT CURRENT_DATE,
    died_on         DATE         NOT NULL DEFAULT CURRENT_DATE,
    status          VARCHAR(12)  NOT NULL DEFAULT 'buried'
                    CHECK (status IN ('buried', 'resurrected')),
    buried_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    resurrected_at  TIMESTAMPTZ  NULL,
    updated_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    CHECK (died_on >= born_on)
);

CREATE INDEX idx_decisions_status_buried ON decisions (status, buried_at DESC);
