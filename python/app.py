from flask import Flask, request
from flask_cors import CORS

app = Flask(__name__)

CORS(app)  # This will enable CORS for all routes

@app.route('/')
def hello():
    pw = request.args.get('pw')
    step = request.args.get('step')
    if (pw != 'wtkInternalCall'):
        return '<h5>PW Error - Python not called correctly</h5>'
    else:
        if (step == '1'):
            return '<h5>This is first step</h5>'
        else:
            return '<h5>This is ' + step + ' step</h5><p>You can add different sections of code in /app.py and call it by passing the `step` of code you want.</p>'
