package main

import (
	"fmt"
	"net"
	"net/http"
	"net/http/fcgi"
	"time"
)

type FastCGIServer struct{}

func isGametime (t time.Time) bool {
	var start, end time.Time
	year := t.Year()
	month := t.Month()
	day := t.Day()
	hour := t.Hour()
	loc := t.Location()
	switch t.Weekday() {
	case time.Monday,time.Tuesday,time.Wednesday,time.Thursday:
		start = time.Date(year, month, day, 20, 0, 0, 0, loc)
		end = time.Date(year, month, day, 23, 59, 59, 999999999, loc)
	case time.Friday:
		start = time.Date(year, month, day, 19, 0, 0, 0, loc)
		end = time.Date(year, month, day+1, 2, 59, 59, 999999999, loc)
	case time.Saturday:
		if hour < 3 {
			start = time.Date(year, month, day-1, 23, 59, 59, 999999999, loc)
			end = time.Date(year, month, day, 2, 59, 59, 999999999, loc)
		} else {
			start = time.Date(year, month, day, 15, 0, 0, 0, loc)
			end = time.Date(year, month, day+1, 2, 59, 59, 999999999, loc)
		}
	case time.Sunday:
		if hour < 3 {
			start = time.Date(year, month, day-1, 23, 59, 59, 999999999, loc)
			end = time.Date(year, month, day, 2, 59, 59, 999999999, loc)
		} else {
			start = time.Date(year, month, day, 17, 0, 0, 0, loc)
			end = time.Date(year, month, day, 23, 59, 59, 999999999, loc)
		}
	}
	return (t.After(start) && t.Before(end))
}

func (s FastCGIServer) ServeHTTP(w http.ResponseWriter, req *http.Request) {
	now := time.Now()
	
	w.Header().Set("Content-Length", "1")

	if isGametime(now) {
		w.Write([]byte("1"))
	} else {
		w.Write([]byte("0"))
	}
}

func main() {
	fmt.Printf("Starting gametime server\n")
	l, _ := net.Listen("tcp", "127.0.0.1:9000")
	b := new(FastCGIServer)
	fcgi.Serve(l, b)
}
