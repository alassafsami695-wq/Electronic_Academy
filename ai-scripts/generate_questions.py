import os
import sys
import json
from groq import Groq

# Force UTF-8 output even on Windows
sys.stdout.reconfigure(encoding='utf-8')

def main():
    # Read text from argv
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No text provided"}, ensure_ascii=False))
        return

    text = sys.argv[1]

    # Load Groq API key from environment variable
    api_key = os.getenv("GROQ_API_KEY")

    if not api_key:
        print(json.dumps({"error": "Missing GROQ_API_KEY environment variable"}, ensure_ascii=False))
        return

    client = Groq(api_key=api_key)

    # Load model from .env
    model = os.getenv("GROQ_MODEL", "llama-3.1-8b-instant")

    prompt = f"""
استخدم النص التالي لإنشاء نوعين من الأسئلة باللغة العربية:
1) أسئلة اختيار من متعدد
2) أسئلة صح وخطأ

أرجو أن يكون ناتجك بصيغة JSON فقط، وبدون أي نص إضافي خارج JSON، بالشكل التالي:

{{
  "multiple_choice": {{
    "questions": [
      {{
        "question": "النص هنا",
        "options": ["خيار 1", "خيار 2", "خيار 3", "خيار 4"],
        "answer": "خيار 1"
      }}
    ]
  }},
  "true_false": {{
    "questions": [
      {{
        "question": "النص هنا",
        "options": ["صح", "خطأ"],
        "answer": "صح"
      }}
    ]
  }}
}}

النص الذي ستولد منه الأسئلة هو:

{text}
"""

    try:
        response = client.chat.completions.create(
            model=model,
            messages=[
                {"role": "user", "content": prompt}
            ],
            temperature=0.3,
        )

        content = response.choices[0].message.content

        # Parse JSON safely
        data = json.loads(content)

        # Print UTF-8 JSON output
        print(json.dumps(data, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({"error": str(e)}, ensure_ascii=False))


if __name__ == "__main__":
    main()
