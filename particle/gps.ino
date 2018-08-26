#include "TinyGPS/TinyGPS.h"

TinyGPS gps;

int led1 = D6;

char szInfo[64];
char gpsDebug[512];

// Every 15 seconds
int sleep = 15 * 1000;

void setup(){
  pinMode(led1, OUTPUT);
  Serial1.begin(9600);
  Serial.begin(9600);
  Spark.function("interval", setUpdateInterval);
}

int setUpdateInterval(String command) {
    sleep = command.toInt() * 1000;
    return sleep;
}

void loop() {
    bool isValidGPS = false;

    int p = 0;
    for (unsigned long start = millis(); millis() - start < 1000;){
        // digitalWrite(led1, HIGH);
        // Check GPS data is available
        while (Serial1.available()){
            char c = Serial1.read();
            // Serial.print(c);
            // gpsDebug[p++] = c;
            
            // parse GPS data
            if(gps.encode(c))
                isValidGPS = true;
        }
        // digitalWrite(led1, LOW);
    }
    //gpsDebug[p] = '\0';
    //Serial.print("\n");

    // If we have a valid GPS location then publish it
    if (isValidGPS){
        float lat, lon;
        unsigned long age;
    
        gps.f_get_position(&lat, &lon, &age);
        
        sprintf(szInfo, "{\"lat\":%.6f,\"lng\":%.6f,\"sat\":%d,\"dop\":%d,\"slp\":%d}", 
          (lat == TinyGPS::GPS_INVALID_F_ANGLE ? 0.0 : lat), 
          (lon == TinyGPS::GPS_INVALID_F_ANGLE ? 0.0 : lon),
          gps.satellites(),
          gps.hdop(),
          sleep/1000
        );
        digitalWrite(led1, HIGH);
        delay(100);
        digitalWrite(led1, LOW);
        delay(100);
        digitalWrite(led1, HIGH);
        delay(100);
        digitalWrite(led1, LOW);
        Spark.publish("gps", szInfo, PRIVATE);
        //Serial.println(szInfo);
    }
    else{
        // Not a valid GPS location
        Spark.publish("debug", "{\"err\":\"no signal\"}", PRIVATE);
    }
    
    // Spark.publish("debug", gpsDebug);
    
    // Sleep for some time
    delay(sleep);
}
