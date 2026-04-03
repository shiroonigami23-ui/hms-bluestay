import hashlib

raw = "if0_41453030_Hotel_management|if0_41453030|force"
print(hashlib.sha256(raw.encode()).hexdigest())
