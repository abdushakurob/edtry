# Edtry: AI-Enhanced Learning Platform

Edtry is an intelligent learning platform that helps teachers create courses and enables students to learn with AI-powered assistance. The app provides educators and learners with integrated AI capabilities for contextual question answering.

<!-- ![Edtry Platform](https://example.com/edtry-screenshot.png) -->

## About the App

Edtry is a learning application that helps students ask questions about their lessons and receive intelligent responses based on the lesson content. The platform uses Retrieval-Augmented Generation (RAG) to ensure that AI responses are grounded in the actual course materials rather than generic information.

Built with:
- **Laravel 12** for the backend architecture
- **Vue.js 3** with Composition API for the frontend interface
- **FastAPI** Python microservice for text chunking
- **Qdrant** vector database for storing embeddings
- **TogetherAI API** for access to embedding and LLM models

## Features

### For Teachers
- Create and manage courses with structured lessons
- Write the lesson text and the title
- Edit and update course materials

### For Students
- Browse and enroll in available courses
- Track learning progress with completion indicators
- Ask questions about lesson content and receive AI-powered answers
- Get intelligent next-lesson suggestions based on queries

### AI Integration
- Context-aware question answering using lesson content
- Automatic chunking and embedding of lesson materials
- Semantic search for retrieving relevant information
- Next topic suggestions based on student questions

## Getting Started

### Prerequisites

- PHP 8.1+
- Composer
- Node.js and npm
- SQLite, MySQL, or PostgreSQL database
- TogetherAI API key for access to embedding and LLM models
- Qdrant instance (cloud or self-hosted)

### Installation

1. Clone the repository
```bash
git clone https://github.com/abdushakurob/edtry.git
cd edtry
```

2. Install PHP dependencies
```bash
composer install
```

3. Install JavaScript dependencies
```bash
yarn install
```

4. Configure environment variables
```bash
cp .env.example .env
# Edit .env file with your database credentials and API keys
```

5. Run database migrations
```bash
php artisan migrate
```

6. Build frontend assets
```bash
yarn run dev
```

7. Start the development server
```bash
php artisan serve
```

8. Access the application at `http://localhost:8000`

### Environment Variables

The following environment variables are required for AI functionality:

```
TOGETHER_API_KEY=your_together_ai_api_key
TOGETHER_API_URL=https://api.together.xyz/v1
QDRANT_API_KEY=your_qdrant_api_key
QDRANT_HOST=your_qdrant_instance_url
QDRANT_COLLECTION=lesson
CHUNK_URL=your_chunking_service_url
EDTRY_INTERNAL_API_KEY=your_internal_api_key
```

### AI Models and Integration

Edtry uses several AI components to provide its intelligent learning experience:

#### Text Chunking Service
- **Service**: Custom [FastAPI microservice](github.com/abdushakurob/edtry-chunker)
- **Purpose**: Breaks down lesson content into manageable chunks for embedding
- **Process**:
  1. Receives lesson text from the Laravel backend
  2. Processes text into semantic chunks
  3. Returns chunks for embedding and storage

#### Embedding Model
- **Model**: BAAI/bge-base-en-v1.5 (via Together AI)
- **Purpose**: Converts text chunks into vector embeddings for semantic search
- **Use Cases**: 
  - Converting lesson content into searchable vectors
  - Converting student questions into vectors for similarity matching

#### LLM (Large Language Model)
- **Model**: deepseek-ai/DeepSeek-R1-Distill-Llama-70B-free (via Together AI)
- **Purpose**: Generates contextual responses to student questions
- **Features**:
  - Responses are grounded in the course material
  - Conversational teaching style
  - Suggests relevant next lessons when appropriate

#### Vector Database
- **Service**: Qdrant
- **Collection**: 'lesson' (configured via QDRANT_COLLECTION)
- **Purpose**: Stores and retrieves lesson content embeddings
- **Features**:
  - Fast similarity search for finding relevant lesson content
  - Filtering by course_id and lesson_id for targeted responses
  - Support for complex queries to find related content across courses

### Getting API Keys

To fully utilize Edtry's AI features, you'll need to obtain the following API keys:

#### Together AI
1. Create an account at [Together AI](https://www.together.ai/)
2. Navigate to your account settings to generate an API key
3. Add this key to your `.env` file as `TOGETHER_API_KEY`

#### Qdrant Vector Database
1. Either:
   - Set up a [Qdrant Cloud](https://qdrant.tech/) account (recommended for easy setup)
   - Self-host Qdrant using Docker or native installation
2. Create a new collection named 'lesson' (or your preferred name)
3. Set up API keys in your Qdrant instance
4. Add the host URL and API key to your `.env` file

#### Internal API Key and FastAPI Chunking Service
1. Generate a secure random string to use as your internal API key
2. Add this key to your `.env` file as `EDTRY_INTERNAL_API_KEY`
3. This key is used to authenticate API requests between the Laravel backend and the FastAPI chunking service
4. Set up the FastAPI chunking service and add its URL to your `.env` file as `CHUNK_URL`

##### Example API call to the FastAPI chunking service:
```bash
curl -X POST ${CHUNK_URL}/chunk \
  -H "Content-Type: application/json" \
  -H "X-Internal-API-Key: your_api_key_here" \
  -d '{
    "course_id": 123,
    "lesson_id": 456,
    "lesson_title": "Introduction to Machine Learning",
    "lesson_content": "Machine learning is a branch of artificial intelligence...",
    "type": "created"
  }'
```

The FastAPI chunking service processes lesson content into semantic chunks and returns them to the Laravel backend. The same internal API key is used both for sending requests to the FastAPI service and for authenticating incoming requests from the FastAPI service to the Laravel backend.

## Project Architecture

### Folder Structure

```
edtry/
├── app/                   # Laravel application code
│   ├── Http/
│   │   ├── Controllers/   # API and web controllers
│   │   └── Middleware/    # Request middleware (auth, roles)
│   └── Models/            # Eloquent data models
├── database/
│   ├── migrations/        # Database schema definitions
│   └── factories/         # Model factories for testing
├── resources/
│   ├── js/
│   │   ├── Components/    # Reusable Vue components
│   │   └── Pages/         # Vue pages for each route
│   ├── views/             # Blade templates
│   │   ├── auth/          # Authentication views
│   │   ├── student/       # Student-specific views
│   │   └── teacher/       # Teacher-specific views
│   └── css/               # Stylesheet assets
└── routes/
    └── web.php            # Application routes
```

### How the Frontend Works

The frontend is built using Vue.js 3 with Composition API and integrated within the Laravel Blade environment. It is a hybrid setup where Vue components are mounted into specific Blade views:

- All Vue-related code is located in the `/resources/js` folder of the Laravel app.
- This is split into two main folders:
  - `components/`: Contains reusable UI components like buttons, modals, chat input etc.
  - `pages/`: Contains full page views, such as Dashboard, Course Views, Lesson Pages, and so on.
- Vue pages are embedded into Blade templates and mounted via specific `div` containers with `id` selectors.
- Routing is handled by Laravel's backend router (`web.php`), so each route serves a specific Blade file which then mounts a Vue component. No Vue Router is used.

Example: The `/` route loads `home.blade.php`, which in turn loads and mounts the `Home.vue` component.

### Role-Based Access
- After login, Laravel determines the user's role (Teacher or Student) and serves the appropriate dashboard.
- Backend routes automatically conditionally render pages based on user roles using the `CheckRole` middleware.

### Axios Integration
- All API calls (course creation, lesson upload, ask-AI) are handled using Axios.

## API Structure

### Authentication Endpoints
- `POST /login`: Login with credentials
- `POST /signup`: Register new account
- `POST /logout`: End user session
- `GET /me`: Get current user information

### Teacher API Routes
- `POST /api/teacher/courses`: Create a new course with lessons
- `GET /api/teacher/courses`: Get all teacher-created courses
- `PUT /api/teacher/courses/{id}`: Update course details
- `GET /api/teacher/courses/{id}`: Get course details
- `DELETE /api/teacher/courses/{id}`: Delete a course
- `GET /api/teacher/courses/{id}/students`: List enrolled students

### Student API Routes
- `GET /api/student/courses`: Get all enrolled courses
- `GET /api/student/courses/{id}`: Get course details (including lessons)
- `POST /api/student/courses/{id}/enroll`: Enroll in a course
- `GET /api/student/available-courses`: Get all available courses
- `GET /api/student/lessons/{id}`: Get lesson details
- `POST /api/student/lessons/{id}/complete`: Mark a lesson as completed

### AI API Routes
- `POST /api/chunked`: Process chunked lessons for vector database
- `POST /ask/ai`: Submit a question for AI-powered response

## AI System Architecture

Edtry uses a Retrieval-Augmented Generation (RAG) system to allow students to ask questions about specific lessons and receive relevant AI-generated answers.

### AI Processing Flow

#### 1. Content Processing

When a teacher creates or updates lesson content:

- The lesson text is sent to a [FastAPI Python microservice](https://github.com/abdushakurob/edtry-chunker)
- The service uses RecursiveChunker (from Chonkie) to split the lesson into semantic chunks
- Chunks respect sentence boundaries and maintain context while keeping size around 400 tokens
- Tokenization is handled using BAAI/bge-base-en-v1.5 for consistency with the embedding model
- The chunks are returned to Laravel with metadata like chunk_index, course_id, lesson_id and title

Example chunking response:
```json
{
  "title": "Introduction to Photosynthesis",
  "text": "...full lesson text...",
  "course_id": 3,
  "lesson_id": 12,
  "type": "updated",
  "chunks": [
    { "text": "Photosynthesis is the process by which...", "chunk_index": 0 },
    { "text": "This process occurs in the chloroplasts...", "chunk_index": 1 }
  ]
}
```

#### 2. Vectorization & Storage

- Laravel receives the chunked content and forwards it to TogetherAI's embedding API
- The API converts each text chunk into a vector embedding (numerical representation) using models hosted on TogetherAI
- Embeddings are stored in Qdrant vector database along with metadata
- The metadata enables filtering by course, lesson, and other attributes during retrieval

#### 3. Question Processing

When a student asks a question:

- The question is converted into an embedding vector using TogetherAI
- Semantic search is performed in Qdrant to find the most relevant chunks
- Results are filtered by course_id and lesson_id to maintain context
- For suggested next lessons, a separate query finds relevant content outside the current lesson
- A prompt template combines the base prompt, student question, retrieved chunks, and suggested lessons
- This prompt is sent to an LLM model hosted on the TogetherAI platform for response generation
- The response is sent to the student chat

#### 4. Response Delivery

- Answers are grounded in the actual lesson content, improving accuracy
- Next lesson suggestions are included when relevant to the question
- Responses clearly indicate when information is not available in the current lesson

This RAG architecture ensures that AI responses stay focused on actual course content rather than generic information, creating a personalized learning experience closely tied to the curriculum.


