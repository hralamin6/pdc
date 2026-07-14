# PSTU Dawah Platform — Advanced Quiz System

The Quiz System is a comprehensive, AI-powered assessment engine built directly into the Dawah platform. It supports both self-paced asynchronous quizzes and real-time live events, fully integrated with the gamification and Halaqah ecosystem.

---

## 🎯 Core Capabilities

- **Dual-Mode Engine:** Support for Async (self-paced within a timeframe) and Live (WebSocket synchronized real-time) quizzes.
- **AI-Powered Generation:** Instantly generate questions from raw text, books, or Halaqah session details using any configured AI Provider (OpenAI, Gemini, Anthropic) via `Laravel AI SDK`.
- **4 Question Types:** 
  1. Multiple Choice (MCQ)
  2. Multi-Select (Select all that apply)
  3. True/False
  4. Short-Text (Open-ended, AI auto-graded with human override)
- **Gamification Engine:** Points for passing, rank-based bonus points (1st/2nd/3rd place bonuses), 20% multiplier for perfect scores, and integrated Daily Streak updates.
- **Strict Security:** Server-synced countdown timers, negative marking support, and auto-submission on timeout.

---

## 🛠️ Usage Guide: For Admins & Mentors

### 1. Creating a Quiz
1. Go to **Quizzes → Manage Quizzes**.
2. Click **"Create Quiz"**.
3. **Configuration Options:**
   - **Mode:** Choose `Async` or `Live`.
   - **Attach to:** Link the quiz to a specific `Halaqah` (Session) or `HalaqahSeries` (Course).
   - **Settings:** Configure negative marking, total time limit, shuffle logic, pass marks, and rank-based bonus JSON.
   
### 2. Building Questions (The AI Builder)
1. Open the **"Build Questions"** panel for your Draft quiz.
2. **Manual Creation:** Click "Add Blank Question" to type out the question, options, and explanations yourself.
3. **AI Generation (Recommended):** 
   - Click **"Generate via AI"**.
   - Select your preferred AI Provider and Model (e.g., `gemini` -> `gemini-2.5-pro`).
   - Choose the number of questions, difficulty level, and which question types to include.
   - Supply context (type a topic, paste text, or select an existing Library book).
   - Click generate. The AI will build the questions and options in strict JSON and inject them directly into your builder.

### 3. Hosting a Live Quiz
1. Set the quiz mode to `Live` and publish it.
2. In the Quiz Manager, click the **"Host Panel"** button.
3. **Waiting Room Phase:** As users click "Join Live Quiz", you will see them populate in your host dashboard.
4. **Start Event:** Click **"Start Live Quiz"**. A WebSocket event (`QuizLiveStarted`) will broadcast to all waiting participants, instantly starting their timers and revealing the first question.
5. **During the Quiz:** 
   - Watch the live **Answer Progress per Question** bar chart.
   - Click **"Push Leaderboard"** periodically to update participants' sidebar ranks in real-time.
6. **Ending:** You can let the timer run out, or click **"Force End Quiz"** to automatically submit everyone's current progress and lock the session.

### 4. Grading Short-Text Answers
For quizzes containing open-ended (`short_text`) questions:
1. Navigate to **Quizzes → Grade Answers**.
2. You will see a list of pending answers.
3. The system automatically displays the **Student's Text**, alongside the **AI's Confidence Grade (0-100%)** and the **AI's Reasoning**.
4. You can manually enter an Admin Grade (0 to 1) and click confirm.
5. **Bulk Action:** If you trust the AI, click **"Auto-Confirm High Confidence"** to instantly approve all answers where the AI's grade is 75% or higher.

---

## 👤 Usage Guide: For Members / Students

### 1. Finding Quizzes
- **Sidebar:** Click **"Browse Quizzes"** to see all available quizzes you have access to.
- **Halaqah Pages:** If a session has a quiz attached, a glowing **"Quiz Attached"** badge will appear on its card. Navigating to the session details will reveal a dedicated "Session Quizzes" block.
- **Dashboard Widget:** If a Live Quiz starts, a massive glowing red widget will dynamically pop up on your dashboard urging you to "Join Now".

### 2. Taking an Async Quiz
- Click **"Take Quiz"**.
- Review the rules (Time limit, total marks, negative marking warnings).
- Answer the questions at your own pace. The timer is locked to the server; refreshing the page will not reset it.
- Once finished (or when the timer runs out), your score is instantly calculated (except for pending short-text answers).

### 3. Participating in a Live Quiz
- Click **"Join Live Quiz"**.
- You will be placed in a synchronized Waiting Room.
- Once the host starts the quiz, the interface will automatically switch to the question viewer.
- The sidebar will feature a **Live Leaderboard** that updates via WebSockets as you and other members progress.

### 4. Gamification & Streaks
- **Earning Points:** You earn base points for passing. If you rank in the top 3, you earn bonus points. A 100% score grants an automatic 20% multiplier.
- **Streaks:** Taking a quiz acts as an "Activity". It will automatically increment your `UserStreak` (just like filing a Daily Report), helping you maintain your consistency milestones.

---

## 💻 Technical Architecture & Commands

- **Broadcasting:** Powered by `laravel/reverb`. 
  - To test live quizzes locally, you MUST run: `php artisan reverb:start`.
- **AI Integration:** Powered by the `laravel/ai` SDK. Ensure `config/ai.php` is populated with correct API keys for Gemini/OpenAI.
- **Database Architecture:**
  - `quizzes` (Polymorphic: attaches to `quizzable`)
  - `quiz_questions`
  - `quiz_options`
  - `quiz_attempts`
  - `quiz_answers`

### Relevant Artisan Commands
```bash
# Start WebSockets server for Live Quizzes
php artisan reverb:start

# Seed the database with sample Bangla quizzes (Async & Live)
php artisan db:seed --class=QuizSeeder
```
