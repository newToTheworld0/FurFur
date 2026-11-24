
import tensorflow as tf
import numpy as np

MODEL_PATH = "pet_breed_mobilenet_finetuned.keras"

IMAGE_SIZE = (224, 224)

print("Loading embedding model...")

# Load the trained breed classification model
model = tf.keras.models.load_model(MODEL_PATH)

# Find the Global Average Pooling layer
pooling_layer = next(
    layer for layer in model.layers
    if isinstance(layer, tf.keras.layers.GlobalAveragePooling2D)
)

# Create a model that outputs image embeddings
embedding_model = tf.keras.Model(
    inputs=model.input,
    outputs=pooling_layer.output
)

print("Embedding model loaded!")


def extract_embedding(image_path):

    # Load the image
    image = tf.keras.utils.load_img(image_path)

    # Convert the image into a numerical array
    image = tf.keras.utils.img_to_array(image)

    # Resize the image
    image = tf.image.resize(image, IMAGE_SIZE)

    # Normalize pixel values from 0-255 to 0-1
    image = image / 255.0

    # Add the batch dimension
    image = tf.expand_dims(image, axis=0)

    # Extract the embedding
    embedding = embedding_model.predict(image, verbose=0)[0]

    return embedding
