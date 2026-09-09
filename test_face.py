import cv2
print("OpenCV version:", cv2.__version__)
try:
    print("FR_COSINE:", cv2.FaceRecognizerSF_FR_COSINE)
except AttributeError:
    try:
        print("FR_COSINE (from class):", cv2.FaceRecognizerSF.FR_COSINE)
    except AttributeError as e:
        print("Could not find FR_COSINE constant:", e)
