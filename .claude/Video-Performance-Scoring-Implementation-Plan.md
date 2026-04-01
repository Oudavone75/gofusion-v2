Video Performance Scoring — Implementation Plan
Context
Quiz performance tracking is already implemented. Now we need video challenge performance scoring — measuring whether employees understood video content, not just watched it.

Current Flow (No Understanding Check)
Admin creates an 
ImageSubmissionGuideline
 with mode=video, points, and a keywords JSON field
User submits via POST /image/validate-step with a comment (description text)
ImageValidationStepService::validateStepImage() awards flat full points for video mode — no evaluation of the comment
Goal
After submission, evaluate the user's comment against expected concepts and award proportional points based on understanding percentage.

Key Design Decisions
1. How to Calculate Video Points
Selected approach: Use the points value already set by admin on image_submission_guidelines as the total possible points, then award proportionally based on the number of keywords understood.

earned_points = total_points × (understood_keywords / total_keywords)
percentage = (understood_keywords / total_keywords) × 100
Example: Admin sets 100 points, video has 4 keywords expected, user matches 3 → earns 75 points, percentage = 75%

2. Keyword Storage Structure
Decision: Keep the existing flat structure ["keyword1", "keyword2"] to avoid breaking the frontend Admin panel or CSV Import processes.

Each keyword in the array will represent one "core concept". We will not define explicit synonyms in the database. Instead, the AI during the matching phase will determine if the user's text meant the same thing as the keyword.

3. How to Match User Comments Against Keywords (Semantic Matching)
Decision: Hybrid Approach (PHP Exact/Fuzzy + LLM Prompt for Meaning)

Since we are keeping a flat list of keywords without defining variants, we need an intelligent AI to understand if the user's text implies the concept. Instead of vector embeddings (which require fine-tuning for abstract concepts), we will use an LLM API (like OpenAI gpt-3.5-turbo or gpt-4o-mini, which are very cheap and fast) to evaluate understanding.

Matching Workflow:

PHP Quick Pass (Free): Check if the exact keyword (or a very close spelling via levenshtein()) exists in the user's comment. If yes, mark it as understood automatically.
LLM Deep Pass: For the remaining unmatched keywords, send the user's comment and the list of missing keywords to an LLM.
System Prompt: "You are an evaluator. The user wrote a summary of a video. Did their summary convey the following concepts? Answer Yes/No for each."
Combine Results: Total Understood = (Quick Pass Matches) + (LLM Approved Matches).
Pros:

Admin doesn't need to do any extra work (just input normal keywords).
Import scripts and database stay exactly the same.
Semantic meaning is understood perfectly by the LLM.
4. Integration with Global Campaign Score
Already built! After computing the video score, just call:

php
$this->campaignPerformanceService->updateVideoPerformance($userId, $campaignSeasonId, $videoScorePercentage);
Proposed Implementation Steps
Database
Add columns to image_submission_steps: total_points, percentage, matched_concepts (JSON), campaign_season_id.
Backend Logic
Create VideoPerformanceService:
evaluateUnderstanding($userComment, array $keywords)
Implements the Hybrid matching (PHP fast match -> LLM semantic match).
Returns matched_keywords and calculates the proportional points.
Update ImageValidationStepService::validateStepImage():
Call VideoPerformanceService->evaluateUnderstanding().
Store the results in image_submission_steps.
Run CampaignPerformanceService::updateVideoPerformance().
APIs Needed
Set up an OpenAI API key in .env (OPENAI_API_KEY) to power the LLM evaluation pass.
