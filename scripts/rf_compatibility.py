import json
import sys
import os
import zipfile
import io
import re

def clean_name(name):
    """Normalize lecturer name by removing academic titles and punctuation."""
    if not name:
        return ""
    # Strip common academic prefixes and degrees
    s = re.sub(r'\b(Prof|Dr|Dr\.Eng|Ir|Dra|Drs)\b\.?,?', '', name, flags=re.IGNORECASE)
    s = re.sub(r',?\s*\b(S\.Kom|M\.Kom|S\.T|M\.T|M\.Sc|S\.Si|M\.Si|Ph\.D|M\.Eng|M\.S)\b\.?', '', s, flags=re.IGNORECASE)
    s = re.sub(r'[^a-zA-Z\s]', '', s)
    return " ".join(s.lower().split())

def main():
    try:
        raw_input = sys.stdin.read()
        if not raw_input.strip():
            print(json.dumps({"status": "error", "message": "Empty input"}))
            return

        payload = json.loads(raw_input)
        candidates = payload.get("candidates", [])
        lecturers = payload.get("lecturers", [])

        if not candidates or not lecturers:
            print(json.dumps({"status": "success", "compatibility": {}}))
            return

        model_path = os.path.join(os.path.dirname(__file__), "..", "models", "rf", "paket_model_rf_runtime_private.zip")
        if not os.path.exists(model_path):
            print(json.dumps({"status": "error", "message": f"Model zip not found at {model_path}"}))
            return

        import joblib
        import pandas as pd
        import numpy as np

        with zipfile.ZipFile(model_path) as z:
            m1 = joblib.load(io.BytesIO(z.read("model/penguji_1_target_final.joblib")))

        # Map lecturer names in DB to IDs
        # Lecturers: [{"id": 1, "name": "..."}, ...]
        clean_db_lecturers = []
        for l in lecturers:
            clean_db_lecturers.append({
                "id": l["id"],
                "clean": clean_name(l.get("name", "")),
                "orig": l.get("name", "")
            })

        classes = m1.named_steps['rf'].classes_
        class_to_db_id = {}
        for c in classes:
            c_clean = clean_name(c)
            for d in clean_db_lecturers:
                if c_clean == d["clean"] or c_clean in d["clean"] or d["clean"] in c_clean:
                    class_to_db_id[c] = d["id"]
                    break

        # Prepare batch dataframe for inference
        rows = []
        for cand in candidates:
            # Map Jenis_Seminar: 'sempro' -> 'Sempro', 'sidang' -> 'Sidang TA'
            raw_tipe = str(cand.get("tipe", "")).strip().lower()
            jenis = "Sempro" if "sempro" in raw_tipe else "Sidang TA"
            judul = str(cand.get("judul") or "Tidak diketahui").strip()
            topik = str(cand.get("kbk") or "Tidak diketahui").strip()
            rows.append({
                "Judul": judul,
                "Jenis_Seminar": jenis,
                "topik_model_v4": topik
            })

        df = pd.DataFrame(rows)
        proba_matrix = m1.predict_proba(df)

        compatibility = {}
        for idx, cand in enumerate(candidates):
            p_id = cand["pendaftaran_id"]
            proba = proba_matrix[idx]
            max_p = np.max(proba) if np.max(proba) > 0 else 1.0

            cand_scores = {}
            for class_idx, class_name in enumerate(classes):
                db_id = class_to_db_id.get(class_name)
                if db_id is not None:
                    # Scaled score relative to max probability in distribution, min 0.1, max 1.0
                    raw_score = float(proba[class_idx]) / max_p
                    cand_scores[db_id] = round(max(0.1, min(1.0, raw_score)), 4)

            compatibility[p_id] = cand_scores

        print(json.dumps({
            "status": "success",
            "matched_lecturers": len(class_to_db_id),
            "compatibility": compatibility
        }))

    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))

if __name__ == "__main__":
    main()
