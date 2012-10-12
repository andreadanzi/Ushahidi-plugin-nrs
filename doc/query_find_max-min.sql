// SOPRA E SOTTO MAX E MIN
CREATE VIEW nrs_overlimits AS 
SELECT 
nrs_datapoint.nrs_environment_id,
nrs_datapoint.nrs_node_id,
nrs_datapoint.nrs_datastream_id,
nrs_datapoint.id as nrs_datapoint_id,
nrs_datapoint.sample_no,
constant_value + (nrs_datapoint.value_at - lambda_value)*factor_value AS calculated_value,
nrs_datastream.max_value,
nrs_datastream.min_value,
nrs_datapoint.datetime_at,
nrs_datapoint.updated
FROM nrs_datapoint, nrs_datastream 
WHERE 
nrs_datastream.id = nrs_datapoint.nrs_datastream_id AND (
constant_value + (nrs_datapoint.value_at - lambda_value)*factor_value <= nrs_datastream.min_value 
OR
constant_value + (nrs_datapoint.value_at - lambda_value)*factor_value >= nrs_datastream.max_value )
ORDER BY nrs_datapoint.nrs_datastream_id, nrs_datapoint.datetime_at, nrs_datapoint.updated, nrs_datapoint.sample_no;

SELECT count( * ) AS overlimits_no, CONVERT( sum( abs(
CASE WHEN calculated_value >= max_value
THEN calculated_value - max_value
ELSE min_value - calculated_value
END ) ) / sum( abs( max_value - min_value ) ) , DECIMAL( 10, 3 ) ) AS overlimits_weight, 
nrs_environment_id, nrs_node_id, nrs_datastream_id, updated
FROM nrs_overlimits
GROUP BY nrs_environment_id, nrs_node_id, nrs_datastream_id, updated
ORDER BY updated DESC , nrs_datastream_id ASC


SELECT count( * ) , nrs_environment_id , nrs_node_id , nrs_datastream_id , updated
FROM nrs_overlimits
GROUP BY nrs_environment_id , nrs_node_id , nrs_datastream_id , updated
ORDER BY updated DESC , nrs_datastream_id ASC


// MEDIA
SELECT AVG(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value) AS datapoint_avg
FROM 
nrs_datapoint,
nrs_datastream
WHERE 
nrs_datastream.id = nrs_datastream_id AND nrs_datapoint.nrs_datastream_id=3599 

SELECT AVG(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value) AS datapoint_avg,
STDDEV_POP(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value) AS datapoint_stdv,
AVG(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value)+STDDEV_POP(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value) AS UPPER,
AVG(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value) -STDDEV_POP(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value) AS LOWER,
MIN(nrs_datapoint.datetime_at)
FROM 
nrs_datapoint,
nrs_datastream
WHERE 
nrs_datastream.id = nrs_datastream_id AND nrs_datapoint.nrs_datastream_id=3599 AND
sample_no => 267 AND sample_no <=1375


