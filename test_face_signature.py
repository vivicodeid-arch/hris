import cv2
import os

yunet_path = "storage/models/yunet.onnx"
sface_path = "storage/models/sface.onnx"

# Try different ways of calling create
print("Attempting to call cv2.FaceDetectorYN.create...")

try:
    # 1. Positional arguments
    detector = cv2.FaceDetectorYN.create(
        yunet_path,
        "",
        (320, 320),
        0.6,
        0.3,
        5000
    )
    print("Success with method 1 (positional)")
except Exception as e:
    print("Failed method 1:", e)

try:
    # 2. Positional with backend/target
    detector = cv2.FaceDetectorYN.create(
        yunet_path,
        "",
        (320, 320),
        0.6,
        0.3,
        5000,
        0, # backend
        0  # target
    )
    print("Success with method 2 (positional with backend/target)")
except Exception as e:
    print("Failed method 2:", e)

try:
    # 3. Named arguments but different names
    detector = cv2.FaceDetectorYN.create(
        model=yunet_path,
        config="",
        input_size=(320, 320),
        score_threshold=0.6,
        nms_threshold=0.3,
        top_k=5000
    )
    print("Success with method 3 (snake_case named arguments)")
except Exception as e:
    print("Failed method 3:", e)
