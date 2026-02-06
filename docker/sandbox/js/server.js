const express = require('express');
const { VM } = require('vm2');
const app = express();

app.use(express.json());

app.post('/run', (req, res) => {
    const { code, context } = req.body;

    try {
        // Create a VM instance
        // timeout: 1000ms prevents infinite loops
        // sandbox: injects the context variables directly
        const vm = new VM({
            timeout: 1000,
            sandbox: context
        });

        // We wrap the user code in a function to ensure it returns a value
        // The user code is expected to be an expression or a script that returns something
        const wrappedCode = `(function() { ${code} })()`;

        const result = vm.run(wrappedCode);

        res.json({ result: result });

    } catch (error) {
        // Return 400 so the Laravel Service knows it failed
        res.status(400).json({
            error: error.message,
            stack: error.stack
        });
    }
});

app.listen(3000, () => {
    console.log('JS Sandbox listening on port 3000');
});
