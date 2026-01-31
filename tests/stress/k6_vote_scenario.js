import http from 'k6/http';
import { check, sleep } from 'k6';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.4.0/index.js';

// Configuration
export const options = {
    stages: [
        { duration: '30s', target: 50 },  // Ramp up to 50 users
        { duration: '1m', target: 100 },  // Stay at 100 users (Peak Load)
        { duration: '30s', target: 0 },   // Ramp down
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // 95% requests should be below 2s
        http_req_failed: ['rate<0.01'],    // Error rate < 1%
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost/webvote'; // Change this via CLI

export default function () {
    // 1. Load Page Assets (Simulated)
    // group('Load Page', function () {
    //   http.get(`${BASE_URL}/index.html`);
    // });

    // 2. Get Performers List
    let performers = [];
    const resPerformers = http.get(`${BASE_URL}/api/performers.php`);
    check(resPerformers, {
        'performers loaded': (r) => r.status === 200,
    });

    try {
        performers = resPerformers.json();
    } catch (e) {
        // console.error('Failed to parse performers');
    }

    // 3. Check Session Status
    const resSession = http.get(`${BASE_URL}/api/session.php`);
    check(resSession, {
        'session check success': (r) => r.status === 200,
    });

    // Simulate user reading/thinking time (5-10s)
    sleep(randomIntBetween(5, 10));

    // 4. Generate Random Vote Data
    // Select 3 random unique performers if available, else placeholders
    let selectedIds = [1, 2, 3];
    if (performers && performers.length >= 3) {
        // Shuffle and pick 3
        const shuffled = performers.sort(() => 0.5 - Math.random());
        selectedIds = shuffled.slice(0, 3).map(p => p.id);
    }

    const payload = JSON.stringify({
        voter: `StressUser_${__VU}_${__ITER}`, // Unique name per virtual user
        fingerprint: `fp_${__VU}_${Math.random()}`, // Mock fingerprint
        votes: {
            rank1: selectedIds[0],
            rank2: selectedIds[1],
            rank3: selectedIds[2]
        },
        timestamp: new Date().toISOString()
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
        },
    };

    // 5. Submit Vote
    const resVote = http.post(`${BASE_URL}/api/votes.php`, payload, params);

    check(resVote, {
        'vote submitted': (r) => r.status === 200,
        'vote success': (r) => r.body.includes('success'),
    });

    // Wait before next iteration or end
    sleep(1);
}
