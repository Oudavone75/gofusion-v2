# GoFusion API Documentation v1

**Base URL:** `/api/v1`

**All endpoints use the `set.lang` middleware** for automatic language detection (defaults to French).

## Response Format

All API responses follow this standardized structure:

```json
{
    "status": true/false,
    "message": "Human readable message",
    "result": {} or [],
    "code": 200
}
```

## Authentication

Most endpoints require authentication via **Laravel Sanctum**. Include the bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

---

## Table of Contents

1. [Authentication & User Management](#authentication--user-management)
2. [Company Management](#company-management)
3. [Go Sessions](#go-sessions)
4. [Step Types](#step-types)
   - [Quiz Steps](#quiz-steps)
   - [Image Validation Steps](#image-validation-steps)
   - [Event Steps](#event-steps)
   - [Challenge Steps](#challenge-steps)
   - [Spin Wheel Steps](#spin-wheel-steps)
   - [Survey Feedback Steps](#survey-feedback-steps)
5. [Inspiration Challenges](#inspiration-challenges)
6. [Campaign Seasons & Leaderboard](#campaign-seasons--leaderboard)
7. [User Progress & Scores](#user-progress--scores)
8. [Carbon Footprint](#carbon-footprint)
9. [Withdrawal Requests](#withdrawal-requests)
10. [News](#news)
11. [Posts (Social Feed)](#posts-social-feed)
12. [Notifications](#notifications)
13. [Company Contact](#company-contact)

---

## Authentication & User Management

### Public Endpoints

#### POST `/register`
Register a new user account.

**Request Body:**
```json
{
    "first_name": "string (required, max:255)",
    "last_name": "string (nullable, max:255)",
    "username": "string (required, unique)",
    "email": "string (required, email, unique)",
    "city": "string (required, max:255)",
    "dob": "date (required, YYYY-MM-DD, before or equal today)",
    "session_time_duration_id": "integer (required)",
    "referral_source": "string (nullable, max:255)",
    "password": "string (required, 8-16 chars, mixed case, numbers, symbols)"
}
```

**Response (201):**
```json
{
    "status": true,
    "message": "User registered successfully",
    "result": {
        "user": { /* User details */ },
        "token": "sanctum_token_here"
    },
    "code": 201
}
```

#### POST `/check-username`
Check if a username is available.

**Request Body:**
```json
{
    "username": "string"
}
```

#### GET `/cities-list`
Get list of available cities.

**Query Parameters:**
- `lang` (optional): Language code (en/fr)

#### GET `/session-time-duration-list`
Get available session time durations for user onboarding.

#### GET `/lanaguages-list`
Get list of supported languages.

#### GET `/modes-list`
Get available user modes.

---

### Authentication Endpoints

#### POST `/auth/login`
Login with credentials.

**Request Body:**
```json
{
    "email": "string (required, email)",
    "password": "string (required)"
}
```

**Response (200):**
```json
{
    "status": true,
    "message": "Login successful",
    "result": {
        "user": { /* User details */ },
        "token": "sanctum_token_here"
    },
    "code": 200
}
```

#### POST `/auth/forgot-password`
Request password reset OTP.

**Request Body:**
```json
{
    "email": "string (required, email)"
}
```

#### POST `/auth/resend-otp`
Resend OTP for password reset.

**Request Body:**
```json
{
    "email": "string (required, email)"
}
```

#### POST `/auth/verify-otp`
Verify OTP code.

**Request Body:**
```json
{
    "email": "string (required, email)",
    "otp": "string (required, 4 digits)"
}
```

#### POST `/auth/reset-password`
Reset password after OTP verification.

**Request Body:**
```json
{
    "email": "string (required, email)",
    "otp": "string (required)",
    "password": "string (required, 8-16 chars with symbols, mixed case, numbers)",
    "password_confirmation": "string (required)"
}
```

---

### Protected Authentication Endpoints

**Requires:** `Authorization: Bearer {token}`

#### POST `/auth/logout`
Logout and revoke current token.

#### POST `/auth/change-language`
Change user's preferred language.

**Request Body:**
```json
{
    "language_id": "integer (required)"
}
```

#### POST `/auth/assing-user-mode`
Assign or update user mode.

**Request Body:**
```json
{
    "mode_id": "integer (required)"
}
```

#### GET `/auth/company-departments-list/{company_id}`
Get departments for a specific company.

**Path Parameters:**
- `company_id`: Company ID

#### DELETE `/auth/delete-account`
Delete user account permanently.

#### POST `/auth/update-profile`
Update user profile information.

**Request Body:**
```json
{
    "first_name": "string (optional)",
    "last_name": "string (optional)",
    "city": "string (optional)",
    "dob": "date (optional)",
    "profile_picture": "file (optional)"
}
```

#### POST `/auth/change-password`
Change user password.

**Request Body:**
```json
{
    "current_password": "string (required)",
    "password": "string (required, 8-16 chars)",
    "password_confirmation": "string (required)"
}
```

#### POST `/auth/update-activity`
Update user activity status.

#### GET `/user-scores`
Get current user's scores and points.

**Response:**
```json
{
    "status": true,
    "result": {
        "total_points": 1250,
        "level": "Active 🍎",
        "rank": 15
    }
}
```

#### GET `/user-details`
Get detailed information about the authenticated user.

---

## Company Management

**Requires Authentication**

#### POST `/company/code-verification`
Verify company code to link user to a company.

**Request Body:**
```json
{
    "company_code": "string (required)"
}
```

#### POST `/company/pending-company-assign-request`
Request assignment to a company (requires approval).

**Request Body:**
```json
{
    "company_id": "integer (required)",
    "department_id": "integer (required)"
}
```

#### GET `/company/departments`
Get departments for the user's associated company.

---

## Go Sessions

**Requires Authentication**

Go Sessions are structured workflows containing multiple steps that users complete to earn points.

#### GET `/sessions/list/{campaign_season_id}`
Get all sessions for a specific campaign season.

**Path Parameters:**
- `campaign_season_id`: Campaign Season ID

**Response:**
```json
{
    "status": true,
    "result": [
        {
            "id": 1,
            "title": "Weekly Sustainability Challenge",
            "description": "Complete all steps to earn rewards",
            "total_steps": 5,
            "completed_steps": 2,
            "points": 100
        }
    ]
}
```

#### GET `/sessions/get-session-steps/{go_session_id}`
Get all steps for a specific session.

**Path Parameters:**
- `go_session_id`: Go Session ID

**Response:**
```json
{
    "status": true,
    "result": [
        {
            "id": 1,
            "step_number": 1,
            "step_type": "quiz",
            "title": "Environmental Quiz",
            "is_completed": false
        }
    ]
}
```

#### GET `/sessions/{id}`
Get detailed information about a specific session.

**Path Parameters:**
- `id`: Go Session ID

---

## Step Types

### Quiz Steps

**Requires Authentication**

#### GET `/steps/quizzes/{go_session_step_id}`
Get quiz questions and options for a specific step.

**Path Parameters:**
- `go_session_step_id`: Go Session Step ID

**Response:**
```json
{
    "status": true,
    "result": {
        "quiz_id": 1,
        "questions": [
            {
                "id": 1,
                "question_text": "What is carbon footprint?",
                "options": [
                    {"id": 1, "option_text": "Option A"},
                    {"id": 2, "option_text": "Option B"}
                ]
            }
        ]
    }
}
```

#### POST `/steps/quizzes/{go_session_step_id}`
Submit quiz answers.

**Path Parameters:**
- `go_session_step_id`: Go Session Step ID

**Request Body:**
```json
{
    "quiz_id": "integer (required)",
    "user_result": [
        {
            "question_id": 1,
            "option_id": 2
        }
    ],
    "points": "integer (required)"
}
```

---

### Image Validation Steps

**Requires Authentication**

#### GET `/steps/image/step-details`
Get details for an image validation step.

**Query Parameters:**
- `go_session_step_id`: Step ID

#### POST `/steps/image/upload-step`
Upload an image for validation.

**Request Body (multipart/form-data):**
```json
{
    "go_session_step_id": "integer (required)",
    "image": "file (required, jpg/png)"
}
```

#### POST `/steps/image/validate-step`
Validate uploaded image (AI or manual validation).

**Request Body:**
```json
{
    "image_submission_step_id": "integer (required)",
    "is_valid": "boolean (required)"
}
```

#### POST `/steps/image/appeal-for-manual-validate`
Appeal for manual validation if AI validation fails.

**Request Body:**
```json
{
    "image_submission_step_id": "integer (required)",
    "reason": "string (optional)"
}
```

---

### Event Steps

**Requires Authentication**

#### GET `/steps/event/step-details`
Get event step details.

**Query Parameters:**
- `go_session_step_id`: Step ID

#### POST `/steps/event/image-upload-step`
Upload event participation image.

**Request Body (multipart/form-data):**
```json
{
    "go_session_step_id": "integer (required)",
    "image": "file (required)"
}
```

#### POST `/steps/event/image-validate-step`
Validate event image.

**Request Body:**
```json
{
    "event_submission_step_id": "integer (required)",
    "is_valid": "boolean (required)"
}
```

#### POST `/steps/event/validate-later-event`
Mark event for validation later.

**Request Body:**
```json
{
    "event_submission_step_id": "integer (required)"
}
```

---

### Challenge Steps

**Requires Authentication**

#### GET `/steps/challenges/categories`
Get challenge categories.

#### GET `/steps/challenges/themes`
Get challenge themes.

#### POST `/steps/challenges/create`
Create a challenge step submission.

**Request Body:**
```json
{
    "go_session_step_id": "integer (required)",
    "category_id": "integer (required)",
    "theme_id": "integer (required)",
    "description": "string (optional)"
}
```

---

### Spin Wheel Steps

**Requires Authentication**

#### GET `/steps/spin-wheel/step-details`
Get spin wheel configuration and odds.

**Query Parameters:**
- `go_session_step_id`: Step ID

#### POST `/steps/spin-wheel/user-create`
Submit spin wheel result.

**Request Body:**
```json
{
    "go_session_step_id": "integer (required)",
    "reward_id": "integer (required)",
    "points": "integer (required)"
}
```

---

### Survey Feedback Steps

**Requires Authentication**

#### GET `/steps/survey-feedback/{go_session_step_id}`
Get survey questions.

**Path Parameters:**
- `go_session_step_id`: Step ID

**Response:**
```json
{
    "status": true,
    "result": {
        "survey_id": 1,
        "questions": [
            {
                "id": 1,
                "question_text": "How satisfied are you?",
                "question_type": "single_choice",
                "options": [
                    {"id": 1, "option_text": "Very Satisfied"},
                    {"id": 2, "option_text": "Satisfied"}
                ]
            }
        ]
    }
}
```

#### POST `/steps/survey-feedback/{go_session_step_id}`
Submit survey responses.

**Path Parameters:**
- `go_session_step_id`: Step ID

**Request Body:**
```json
{
    "survey_id": "integer (required)",
    "responses": [
        {
            "question_id": 1,
            "option_id": 2
        }
    ]
}
```

---

## Inspiration Challenges

**Requires Authentication**

User-generated challenges outside of Go Sessions.

#### GET `/inspiration-challenges/themes`
Get available themes for inspiration challenges.

#### GET `/inspiration-challenges/listing`
Get list of inspiration challenges.

**Query Parameters:**
- `theme_id` (optional): Filter by theme
- `limit` (optional): Results per page (default: 15)
- `page` (optional): Page number

#### GET `/inspiration-challenges/detail/{challenge_step_id}`
Get details of a specific inspiration challenge.

**Path Parameters:**
- `challenge_step_id`: Challenge Step ID

#### POST `/inspiration-challenges/upload-image`
Upload image for an inspiration challenge.

**Request Body (multipart/form-data):**
```json
{
    "challenge_step_id": "integer (required)",
    "image": "file (required)"
}
```

#### POST `/inspiration-challenges/validate`
Validate an inspiration challenge submission.

**Request Body:**
```json
{
    "challenge_step_id": "integer (required)",
    "is_valid": "boolean (required)"
}
```

#### POST `/inspiration-challenges/create`
Create a new inspiration challenge.

**Request Body (multipart/form-data):**
```json
{
    "theme_id": "integer (required)",
    "category_id": "integer (required)",
    "title": "string (required)",
    "description": "string (required)",
    "image": "file (optional)"
}
```

---

## Campaign Seasons & Leaderboard

**Requires Authentication**

#### GET `/get-active-campaign-or-season`
Get currently active campaign season.

**Response:**
```json
{
    "status": true,
    "result": {
        "id": 1,
        "title": "Spring 2025 Challenge",
        "start_date": "2025-03-01",
        "end_date": "2025-05-31",
        "is_active": true
    }
}
```

#### GET `/ranking-leader-board`
Get leaderboard rankings for the current campaign.

**Query Parameters:**
- `campaign_season_id` (optional): Filter by campaign
- `department_id` (optional): Filter by department

**Response:**
```json
{
    "status": true,
    "result": {
        "user_rank": 5,
        "user_points": 1250,
        "leaderboard": [
            {
                "rank": 1,
                "user_id": 42,
                "username": "eco_warrior",
                "points": 2500,
                "level": "Legend 🌟"
            }
        ]
    }
}
```

---

## User Progress & Scores

**Requires Authentication**

#### GET `/user-progress`
Get user's overall progress across all sessions.

**Response:**
```json
{
    "status": true,
    "result": {
        "completed_sessions": 5,
        "total_sessions": 10,
        "total_points": 1250,
        "current_level": "Active 🍎",
        "progress_percentage": 50
    }
}
```

---

## Carbon Footprint

**Requires Authentication**

#### GET `/user-carbon-foot-print`
Get user's carbon footprint for the current month.

**Response:**
```json
{
    "status": true,
    "result": {
        "month": "December",
        "year": 2025,
        "total_footprint_kg": 125.5,
        "saved_footprint_kg": 45.2,
        "breakdown": {
            "transport": 80.3,
            "energy": 30.2,
            "waste": 15.0
        }
    }
}
```

#### POST `/save-carbons`
Save carbon footprint data for the user.

**Request Body:**
```json
{
    "transport": "float (optional)",
    "energy": "float (optional)",
    "waste": "float (optional)",
    "date": "date (required, YYYY-MM-DD)"
}
```

---

## Withdrawal Requests

**Requires Authentication**

Users can withdraw earned rewards/points.

#### POST `/create-withdrawal-request`
Create a new withdrawal request.

**Request Body:**
```json
{
    "points": "integer (required, minimum points threshold applies)",
    "withdrawal_method": "string (required)",
    "account_details": "string (required)"
}
```

**Response (201):**
```json
{
    "status": true,
    "message": "Withdrawal request created successfully",
    "result": {
        "request_id": 123,
        "points": 500,
        "status": "pending"
    },
    "code": 201
}
```

#### GET `/withdrawal-requests`
Get user's withdrawal request history.

**Query Parameters:**
- `status` (optional): Filter by status (pending/approved/rejected)
- `limit` (optional): Results per page

**Response:**
```json
{
    "status": true,
    "result": {
        "data": [
            {
                "id": 123,
                "points": 500,
                "status": "pending",
                "requested_at": "2025-12-01 10:30:00",
                "processed_at": null
            }
        ],
        "pagination": { /* pagination info */ }
    }
}
```

---

## News

**Requires Authentication**

#### GET `/news/category-list`
Get all news categories.

#### GET `/news/list`
Get paginated list of news articles.

**Query Parameters:**
- `category_id` (optional): Filter by category
- `limit` (optional): Results per page (default: 15)
- `page` (optional): Page number

**Response:**
```json
{
    "status": true,
    "result": {
        "data": [
            {
                "id": 1,
                "title": "New Sustainability Initiative Launched",
                "excerpt": "Short description...",
                "category": "Environment",
                "published_at": "2025-12-01",
                "image_url": "/storage/news/image.jpg"
            }
        ],
        "pagination": { /* pagination info */ }
    }
}
```

#### GET `/news/detail/{news_id}`
Get detailed news article.

**Path Parameters:**
- `news_id`: News Article ID

---

## Posts (Social Feed)

**Requires Authentication**

Social media-like feed where users can share posts, comment, and react.

#### POST `/posts/create`
Create a new post.

**Request Body (multipart/form-data):**
```json
{
    "content": "string (required)",
    "media[]": "file[] (optional, images/videos)",
    "mentions[]": "integer[] (optional, user IDs to mention)"
}
```

**Response (201):**
```json
{
    "status": true,
    "message": "Post created successfully",
    "result": {
        "post_id": 123,
        "content": "Post content here",
        "created_at": "2025-12-08 10:30:00"
    },
    "code": 201
}
```

#### GET `/posts/list`
Get paginated feed of posts.

**Query Parameters:**
- `user_id` (optional): Filter by specific user
- `limit` (optional): Results per page (default: 15)
- `page` (optional): Page number

**Response:**
```json
{
    "status": true,
    "result": {
        "data": [
            {
                "id": 123,
                "user": {
                    "id": 42,
                    "username": "eco_warrior",
                    "profile_picture": "/storage/profiles/pic.jpg"
                },
                "content": "Just completed my challenge!",
                "media": [
                    {"id": 1, "url": "/storage/posts/image.jpg", "type": "image"}
                ],
                "reactions_count": 15,
                "comments_count": 3,
                "user_reaction": "like",
                "created_at": "2025-12-08 10:30:00"
            }
        ],
        "pagination": { /* pagination info */ }
    }
}
```

#### GET `/posts/detail/{post_id}`
Get detailed post with comments and reactions.

**Path Parameters:**
- `post_id`: Post ID

#### GET `/posts/user-list`
Get posts created by the authenticated user.

**Query Parameters:**
- `limit` (optional): Results per page

#### POST `/posts/{post_id}/update`
Update an existing post.

**Path Parameters:**
- `post_id`: Post ID

**Request Body (multipart/form-data):**
```json
{
    "content": "string (optional)",
    "media[]": "file[] (optional, new media files)"
}
```

#### DELETE `/posts/{post_id}/delete`
Delete a post (only post owner can delete).

**Path Parameters:**
- `post_id`: Post ID

#### POST `/posts/add-comment`
Add a comment to a post.

**Request Body:**
```json
{
    "post_id": "integer (required)",
    "comment": "string (required, max:500)"
}
```

#### POST `/posts/react`
React to a post (like, love, etc.).

**Request Body:**
```json
{
    "post_id": "integer (required)",
    "reaction_type": "string (required, e.g., 'like', 'love', 'celebrate')"
}
```

**Note:** Sending the same reaction again removes it (toggle behavior).

#### POST `/posts/report`
Report inappropriate post content.

**Request Body:**
```json
{
    "post_id": "integer (required)",
    "reason": "string (required)",
    "description": "string (optional)"
}
```

#### DELETE `/posts/delete-post-media/{media_id}`
Delete specific media from a post.

**Path Parameters:**
- `media_id`: Post Media ID

#### POST `/posts/like-comment`
Like or unlike a comment.

**Request Body:**
```json
{
    "comment_id": "integer (required)"
}
```

---

## Notifications

**Requires Authentication**

#### GET `/notifications/list`
Get user's notifications.

**Query Parameters:**
- `limit` (optional): Results per page (default: 20)
- `page` (optional): Page number
- `unread_only` (optional): Boolean to filter unread notifications

**Response:**
```json
{
    "status": true,
    "result": {
        "data": [
            {
                "id": 1,
                "title": "New Challenge Available",
                "message": "Check out the latest sustainability challenge!",
                "type": "challenge",
                "is_read": false,
                "created_at": "2025-12-08 09:00:00",
                "data": {
                    "challenge_id": 42
                }
            }
        ],
        "unread_count": 5,
        "pagination": { /* pagination info */ }
    }
}
```

#### POST `/notifications/test`
Test notification endpoint (for development/testing).

---

## Company Contact

**Requires Authentication**

#### POST `/company-contact`
Submit a contact/support request.

**Request Body:**
```json
{
    "subject": "string (required, max:255)",
    "message": "string (required)",
    "contact_type": "string (optional, e.g., 'support', 'feedback')"
}
```

**Response (201):**
```json
{
    "status": true,
    "message": "Contact request submitted successfully",
    "result": {
        "contact_id": 123,
        "status": "pending"
    },
    "code": 201
}
```

---

## Mention Users

**Requires Authentication**

#### GET `/get-mention-users-list`
Get list of users that can be mentioned in posts.

**Query Parameters:**
- `search` (optional): Search by username
- `limit` (optional): Results limit (default: 20)

**Response:**
```json
{
    "status": true,
    "result": [
        {
            "id": 42,
            "username": "eco_warrior",
            "full_name": "John Doe",
            "profile_picture": "/storage/profiles/pic.jpg"
        }
    ]
}
```

---

## Error Responses

### 400 Bad Request
```json
{
    "status": false,
    "message": "Invalid input data",
    "result": [],
    "code": 400
}
```

### 401 Unauthorized
```json
{
    "status": false,
    "message": "Unauthenticated",
    "result": [],
    "code": 401
}
```

### 403 Forbidden
```json
{
    "status": false,
    "message": "You don't have permission to perform this action",
    "result": [],
    "code": 403
}
```

### 404 Not Found
```json
{
    "status": false,
    "message": "Resource not found",
    "result": [],
    "code": 404
}
```

### 422 Validation Error
```json
{
    "status": false,
    "message": "The given data was invalid",
    "result": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    },
    "code": 422
}
```

### 500 Internal Server Error
```json
{
    "status": false,
    "message": "An error occurred while processing your request",
    "result": [],
    "code": 500
}
```

---

## Rate Limiting

API endpoints are rate limited to prevent abuse:
- **Public endpoints:** 60 requests per minute
- **Authenticated endpoints:** 120 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1702035600
```

---

## Localization

All endpoints support multi-language responses via the `lang` query parameter:

- `lang=en` - English
- `lang=fr` - French (default)

Example:
```
GET /api/v1/news/list?lang=en
```

The `set.lang` middleware automatically detects:
1. Authenticated user's preferred language (from database)
2. `lang` query parameter
3. Falls back to French if neither is provided

---

## Testing with Postman/Insomnia

1. **Set Base URL:** `http://your-domain.com/api/v1`
2. **Add Authorization Header:**
   ```
   Authorization: Bearer {your_token_here}
   ```
3. **Set Accept Header:**
   ```
   Accept: application/json
   ```
4. **For multipart requests (file uploads):**
   - Set Content-Type to `multipart/form-data`
   - Add files to the request body

---

## Changelog

### v1.0.0 (Current)
- Initial API release
- All core features implemented
- Multi-language support (EN/FR)
- Social feed functionality
- Go Sessions with multiple step types
- Carbon footprint tracking
- Leaderboard and campaigns
