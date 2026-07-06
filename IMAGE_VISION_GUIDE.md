# Image Vision Support - Quick Guide

## ✅ What's Implemented

Your AI chat now supports **image vision** with Gemini! Users can upload images and ask questions about them.

## 🎯 How It Works

### For Users
1. Click the **image icon** (📷) in the chat input
2. Select one or more images (JPEG, PNG, WebP)
3. Type your question about the image(s)
4. Press Enter to send

### Example Questions
- "What's in this image?"
- "Describe this photo in detail"
- "What colors do you see?"
- "Read the text in this image"
- "Compare these two images"

## 🤖 Provider Support

| Provider | Vision Support | Models | Notes |
|----------|----------------|--------|-------|
| **Gemini** | ✅ **YES** | All flash models | Best option, marked with (img) |
| **Mistral** | ✅ **YES** | Pixtral models only | pixtral-12b-2409, pixtral-large-2411 |
| **Groq** | ✅ **YES** | Llama 4 models only | llama-4-scout, llama-4-maverick |
| **Pollinations** | ✅ **YES** | All text models | openai, gemini, claude, mistral, deepseek |
| Cerebras | ❌ No | - | No vision capability |
| OpenRouter | ❌ No | - | Not implemented |

**Look for (img) in model names** - These models can analyze images!

## 🔧 Technical Details

### What Was Changed

1. **GeminiService.php**
   - Added `images` parameter to `chat()` method
   - Updated `convertMessagesToGeminiFormat()` to handle images
   - Images are base64 encoded and sent as `inlineData`

2. **MistralService.php**
   - Added vision support for Pixtral models
   - Uses OpenAI-compatible multimodal format
   - Automatically detects Pixtral models

3. **GroqService.php**
   - Added vision support for Llama 4 models
   - Uses OpenAI-compatible multimodal format
   - Automatically detects Llama 4 models

4. **ai-chat.php** (Livewire Component)
   - Reads and encodes images immediately (before Livewire cleanup)
   - Passes base64 encoded data to AI services
   - Stores images with Spatie Media Library

5. **input-area.blade.php**
   - Shows image thumbnails for preview
   - Changed file input to accept images only
   - Added visual feedback with image icon

6. **Model Names**
   - All vision-capable models marked with **(img)**
   - Easy to identify which models support images

### Image Processing
- Images are stored using Spatie Media Library
- Converted to base64 for API transmission
- Sent as `inlineData` with MIME type
- Attached to the last user message

### API Format (Gemini)
```json
{
  "contents": [
    {
      "role": "user",
      "parts": [
        {"text": "What's in this image?"},
        {
          "inlineData": {
            "mimeType": "image/jpeg",
            "data": "base64_encoded_image_data..."
          }
        }
      ]
    }
  ]
}
```

## 📝 Usage Tips

### Best Practices
- **Gemini 2.5 Flash** - Best overall vision quality
- **Mistral Pixtral Large** - Good for European compliance
- **Groq Llama 4** - Ultra-fast vision processing
- Keep images under 10MB
- Supported formats: JPEG, PNG, WebP
- Multiple images per message are supported
- Be specific in your questions
- Look for **(img)** in model names

### Limitations
- Only works with vision-capable models (marked with img)
- Images must be uploaded (no URLs yet)
- Base64 encoding increases payload size
- Processing time depends on image size

## 🚀 Future Enhancements (Not Implemented)

If you want to extend this:
- Image URL support (instead of upload)
- Image optimization/resizing
- PDF to image conversion
- OCR for text extraction

## 🧪 Testing

To test the vision feature:

1. **Choose a Vision Provider**
   - Open Settings
   - Select **Gemini**, **Mistral**, or **Groq**
   - Choose a model with **(img)** in the name

2. **Upload an Image**
   - Click the image icon
   - Select a photo
   - See the thumbnail preview

3. **Ask About It**
   - Type: "What do you see in this image?"
   - Press Enter
   - AI will analyze and describe the image

### Recommended Models:
- **Gemini**: `gemini-2.5-flash (img)`
- **Mistral**: `pixtral-large-2411 (img)`
- **Groq**: `llama-4-scout-17b-16e-instruct (img)`

## 📊 Example Conversation

```
User: [uploads photo of a sunset]
      "Describe this image"

AI: "This image shows a beautiful sunset over the ocean. 
     The sky is painted in vibrant shades of orange, pink, 
     and purple. The sun is just above the horizon, casting 
     a golden reflection on the calm water..."
```

## ⚠️ Important Notes

- **Vision models marked with (img)**: Look for this indicator in model dropdown
- **Provider support**: Gemini (all flash), Mistral (Pixtral), Groq (Llama 4)
- **Storage**: Images are permanently stored in your media library
- **Privacy**: Images are sent to respective AI provider APIs
- **Costs**: Check provider pricing for vision API calls

## 🎉 That's It!

Your AI chat now has vision capabilities with **3 providers**! Just upload images when using vision-capable models (marked with **img**) and ask questions about them.

---

**Quick Start:**
1. Select a vision provider (Gemini/Mistral/Groq)
2. Choose a model with **(img)** indicator
3. Upload image
4. Ask question
5. Get AI analysis! 🎨

**Vision-Capable Models:**
- 📷 Gemini: All flash models
- 📷 Mistral: Pixtral models
- 📷 Groq: Llama 4 models
