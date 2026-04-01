Changes Made
ImportFileDataValidationService.php — Added Keywords to required headers for the ChallengeToComplete sheet, and added validation rule: keywords are required when mode is video.

ImportFileController.php — Updated extractChallengeData to:

Require Keywords header
Extract the comma-separated keywords string when mode is video
Convert it to an array (e.g., "ai coding, claude ai, ai agents" → ["ai coding", "claude ai", "ai agents"])
Pass Keywords in the extracted data
ImportFileDataStoreService.php — Updated handleChallengeStoreData to include keywords when creating the ImageSubmissionGuideline record. Since the model casts keywords as array, the array will be stored as JSON automatically.

Excel Sheet Format Expected
The ChallengeToComplete sheet now needs a Keywords column with comma-separated values like:


ai coding, claude ai, ai agents, coding with ai, ai tools for developers
Keywords are only required for rows where Mode is video.
