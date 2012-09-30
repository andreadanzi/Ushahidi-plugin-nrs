mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355.csv -m "Cantiere Fai II;1355;CAntiere fai dela Paganella;4;;Fai della Paganella;Sopra la strada Comunale;SUD;46.177639;11.065205;1050;"

mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/21.csv -m "Nodo 21;21;Nodo Completo;3;Seconda Tratta Terzo Montante;SUD"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/22.csv -m "Nodo 22;22;Nodo Completo;3;Seconda Tratta Terzo Montante;SUD"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/23.csv -m "Nodo 23;23;Nodo Completo;3;Seconda Tratta Terzo Montante;SUD"

mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/21/datastream/AX.csv -m "Accelerazione X;AX;Accelerazione X;g;acceleration;g;1.67;2.32;-2.002"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/21/datastream/AY.csv -m "Accelerazione Y;AY;Accelerazione Y;g;acceleration;g;1.67;2.32;-2.002"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/21/datastream/AZ.csv -m "Accelerazione Z;AZ;Accelerazione Z;g;acceleration;g;1.67;2.32;-2.002"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/22/datastream/AM.csv -m "Accelerazione MOD;AM;Accelerazione (Modulo);g;acceleration;g;1.67;2.32;-2.002"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/23/datastream/DS.csv -m "Distanza;DS;Distanza Relativa;m;distanza;m;12.6;12.5;12.3"

mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/22/datastream/AM/datapoint/ND.csv -m "1;2012-09-26T13:43.123Z;1.65"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/22/datastream/AM/datapoint/ND.csv -m "2;2012-09-26T13:43.124Z;1.05"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/22/datastream/AM/datapoint/ND.csv -m "3;2012-09-26T13:43.125Z;1.35"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/22/datastream/AM/datapoint/ND.csv -m "4;2012-09-26T13:43.126Z;1.32"
mosquitto_pub -h rockfalldefence.dyndns.org -q 1 -r -t /nrs/v1/env/1355/node/22/datastream/AM/datapoint/ND.csv -m "5;2012-09-26T13:43.127Z;1.54"

/nrs/v1/env/1355/node/21/datastream/AM/datapoint/ND.CSV
