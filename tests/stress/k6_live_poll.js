import http from 'k6/http';
import { check, sleep } from 'k6';

// Configuration
export const options = {
    stages: [
        { duration: '30s', target: 50 },  // Ramp up to 50 users (Simulate 50 people watching)
        { duration: '2m', target: 50 },   // Stay at 50 users for 2 minutes
        { duration: '10s', target: 0 },   // End
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // Response must be fast
        http_req_failed: ['rate<0.01'],    // No errors allowed
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost/webvote';

export default function () {
    // 1. Poll Results (Simulates 3s interval)
    const resResults = http.get(`${BASE_URL}/api/results.php`);
    check(resResults, {
        'results loaded': (r) => r.status === 200,
    });

    // 2. Poll Session (Simulates 5s interval - approximated by random sleep)
    // Since we can't easily have multiple async intervals in one VU in k6 without complexity,
    // we will simulate the *average load* by making requests sequentially with sleep.
    // Real browser does: Results (3s) and Session (5s).
    // Total requests per 15 seconds: 5 Results + 3 Sessions = 8 requests.
    // Avg sleep = 15s / 8 = ~1.875s.
    // Let's just alternate requests with ~3s sleep to simulate "Results" primarily, 
    // and occasionally "Session".

    sleep(1); // Small delay between calls

    const resSession = http.get(`${BASE_URL}/api/session.php`);
    check(resSession, {
        'session loaded': (r) => r.status === 200,
    });

    // Sleep to match the "3s / 5s" polling nature.
    // Users poll Results (3s) implies sleep(3). 
    // We are being a bit aggressive here doing both in one iteration with sleep(3).
    sleep(3);
}
