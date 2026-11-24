
import os
import io
import numpy as np
import tensorflow as tf

import json
import tempfile

from pathlib import Path
from EMBEDDING_EXTRACTOR import extract_embedding

from fastapi import FastAPI, UploadFile, File, HTTPException


# --------------------------------
# API CONFIGURATION
# --------------------------------

app = FastAPI(
    title="Stray Safe Pet Recognition API",
    description="Pet breed classification and individual pet identification.",
    version="1.0.0"
)


# --------------------------------
# MODEL CONFIGURATION
# --------------------------------

MODEL_PATH = os.path.join(
    os.path.dirname(__file__),
    "pet_breed_mobilenet_finetuned.keras"
)

CLASS_NAMES = [
    "Abyssinian",
    "american_bulldog",
    "american_pit_bull_terrier",
    "basset_hound",
    "beagle",
    "Bengal",
    "Birman",
    "Bombay",
    "boxer",
    "British_Shorthair",
    "chihuahua",
    "Egyptian_Mau",
    "english_cocker_spaniel",
    "english_setter",
    "german_shorthaired",
    "great_pyrenees",
    "havanese",
    "japanese_chin",
    "keeshond",
    "leonberger",
    "Maine_Coon",
    "miniature_pinscher",
    "newfoundland",
    "Persian",
    "pomeranian",
    "pug",
    "Ragdoll",
    "Russian_Blue",
    "saint_bernard",
    "samoyed",
    "scottish_terrier",
    "shiba_inu",
    "Siamese",
    "Sphynx",
    "staffordshire_bull_terrier",
    "wheaten_terrier",
    "yorkshire_terrier"
]


# --------------------------------
# LOAD MODEL
# --------------------------------

breed_model = tf.keras.models.load_model(
    MODEL_PATH,
    compile=False
)

print("Breed classification model loaded!")


# --------------------------------
# HOMEPAGE
# --------------------------------

@app.get("/")
def home():
    return {
        "message": "Pet Recognition API is running!",
        "status": "online"
    }


# --------------------------------
# HEALTH CHECK
# --------------------------------

@app.get("/health")
def health_check():
    return {
        "status": "healthy"
    }


# --------------------------------
# BREED PREDICTION
# --------------------------------

@app.post("/predict/breed")
async def predict_breed(file: UploadFile = File(...)):

    image_data = await file.read()

    try:
        image = tf.keras.utils.load_img(
            io.BytesIO(image_data),
            target_size=(224, 224),
            color_mode="rgb"
        )

    except Exception:
        raise HTTPException(
            status_code=400,
            detail="Invalid image file."
        )

    image_array = tf.keras.utils.img_to_array(image)

    image_array = image_array / 255.0

    image_array = np.expand_dims(image_array, axis=0)

    predictions = breed_model.predict(
        image_array,
        verbose=0
    )[0]

    top_indices = np.argsort(predictions)[-3:][::-1]

    results = []

    for index in top_indices:

        results.append({
            "breed": CLASS_NAMES[index],
            "score": float(predictions[index])
        })

    return {
        "filename": file.filename,
        "predictions": results
    }

# Folder containing registered pet embeddings and metadata
REGISTERED_FOLDER = Path(__file__).parent / "data" / "pet_database" / "registered"

# Minimum cosine similarity required to count as a match
SIMILARITY_THRESHOLD = 0.6769


def cosine_similarity(a, b):
    """Calculate cosine similarity between two embedding vectors."""

    denominator = np.linalg.norm(a) * np.linalg.norm(b)

    if denominator == 0:
        return 0.0

    return float(np.dot(a, b) / denominator)


@app.post("/identify")
async def identify_pet(file: UploadFile = File(...)):

    # Read the uploaded image
    image_data = await file.read()

    if not image_data:
        raise HTTPException(
            status_code=400,
            detail="The uploaded image is empty."
        )

    # Save the upload temporarily so extract_embedding() can read it
    temp_path = None

    try:
        with tempfile.NamedTemporaryFile(
            suffix=".jpg",
            delete=False
        ) as temp_file:

            temp_file.write(image_data)
            temp_path = temp_file.name

        # Extract the uploaded image's embedding
        query_embedding = extract_embedding(temp_path)

    except Exception:
        raise HTTPException(
            status_code=400,
            detail="Could not process the uploaded image."
        )

    finally:
        # Delete the temporary image after processing
        if temp_path and os.path.exists(temp_path):
            os.remove(temp_path)

    # Check whether the registered folder exists
    if not REGISTERED_FOLDER.exists():
        raise HTTPException(
            status_code=500,
            detail="Registered pet database folder was not found."
        )

    matches = []

    # Read every registered pet's embedding file
    for embedding_path in REGISTERED_FOLDER.glob("*.npy"):

        metadata_path = embedding_path.with_suffix(".json")

        if not metadata_path.exists():
            continue

        # Load registered embeddings and pet information
        registered_embeddings = np.load(embedding_path)

        with open(metadata_path, "r", encoding="utf-8") as metadata_file:
            metadata = json.load(metadata_file)

        # Compare the query against every registered image
        similarities = [
            cosine_similarity(query_embedding, registered_embedding)
            for registered_embedding in registered_embeddings
        ]

        if not similarities:
            continue

        # Use the highest similarity for this particular pet
        best_similarity = max(similarities)

        if best_similarity >= SIMILARITY_THRESHOLD:
            matches.append({
                "pet_id": metadata["pet_id"],
                "pet_name": metadata["pet_name"],
                "similarity": best_similarity
            })

    # Sort matches from highest to lowest similarity
    matches.sort(
        key=lambda match: match["similarity"],
        reverse=True
    )

    return {
        "filename": file.filename,
        "matches": matches
    }