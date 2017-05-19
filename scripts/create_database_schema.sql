/* create event stream */
CREATE TABLE event_streams (
  no BIGSERIAL,
  real_stream_name VARCHAR(150) NOT NULL,
  stream_name CHAR(41) NOT NULL,
  metadata JSONB,
  category VARCHAR(150),
  PRIMARY KEY (no),
  UNIQUE (stream_name)
);
CREATE INDEX ON event_streams (category);

/* create projections */
CREATE TABLE pizzerias (
  id VARCHAR(36) NOT NULL,
  name VARCHAR(1023) NOT NULL,
  pizzas JSONB,
  PRIMARY KEY (id),
  UNIQUE (name)
);
