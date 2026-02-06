from flask import Flask, request, jsonify
import sys
import io
import json

app = Flask(__name__)

@app.route('/run', methods=['POST'])
def run_script():
    data = request.get_json()
    code = data.get('code', '')
    context = data.get('context', {})

    # Capture stdout to return print() statements if needed
    # Or rely on the script returning a value via a specific variable

    # We will use a restricted dictionary for globals to prevent OS access
    # Note: 'builtins' is dangerous, so we limit it.
    safe_builtins = {
        'print': print,
        'range': range,
        'len': len,
        'int': int,
        'str': str,
        'float': float,
        'list': list,
        'dict': dict,
        'json': json
    }

    local_scope = context.copy()

    try:
        # EXECUTE THE CODE
        # We execute it in the local_scope.
        exec(code, {'__builtins__': safe_builtins}, local_scope)

        # We assume the user code sets a variable named 'result'
        # OR we just return the modified scope.
        # Let's check if 'result' exists in the scope, otherwise return the whole scope (filtered)

        result = local_scope.get('result', None)

        # If result is missing, we try to be smart or just return null
        return jsonify({'result': result})

    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
