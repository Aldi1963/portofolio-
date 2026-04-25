"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import { CheckCircle2, Loader2, Mail, MapPin, Phone, Send } from "lucide-react";
import { personal } from "@/lib/data";
import { SectionHeading } from "../section-heading";

type Status = "idle" | "loading" | "success" | "error";

export function Contact() {
  const [status, setStatus] = useState<Status>("idle");
  const [error, setError] = useState<string>("");

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    setStatus("loading");
    setError("");
    const fd = new FormData(form);
    const payload = {
      name: String(fd.get("name") ?? "").trim(),
      email: String(fd.get("email") ?? "").trim(),
      subject: String(fd.get("subject") ?? "").trim(),
      message: String(fd.get("message") ?? "").trim(),
    };
    try {
      const res = await fetch("/api/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const data = (await res.json().catch(() => ({}))) as { error?: string };
      if (!res.ok) throw new Error(data.error ?? "Gagal mengirim pesan.");
      setStatus("success");
      form.reset();
    } catch (err) {
      setStatus("error");
      setError(err instanceof Error ? err.message : "Gagal mengirim pesan.");
    }
  }

  return (
    <section id="contact" className="relative py-24 sm:py-32">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="Contact"
          title={<>Mari <span className="gradient-text">ngobrol</span></>}
          description="Punya proyek, pertanyaan, atau sekadar ingin sapa? Kirim pesan — saya balas dalam 24 jam."
        />

        <div className="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[1fr_1.4fr]">
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, amount: 0.3 }}
            transition={{ duration: 0.4 }}
            className="space-y-4"
          >
            <ContactItem icon={Mail} label="Email" value={personal.email} href={`mailto:${personal.email}`} />
            <ContactItem icon={Phone} label="Telepon" value={personal.phone} href={`tel:${personal.phone.replace(/\s/g, "")}`} />
            <ContactItem icon={MapPin} label="Lokasi" value={personal.location} />

            <div className="rounded-2xl border border-border bg-card/60 p-5">
              <p className="text-sm font-semibold">Atau lewat sosial media</p>
              <div className="mt-3 grid grid-cols-2 gap-2 text-sm">
                <a className="text-muted-foreground hover:text-foreground" href={personal.social.github} target="_blank" rel="noopener noreferrer">→ GitHub</a>
                <a className="text-muted-foreground hover:text-foreground" href={personal.social.linkedin} target="_blank" rel="noopener noreferrer">→ LinkedIn</a>
                <a className="text-muted-foreground hover:text-foreground" href={personal.social.twitter} target="_blank" rel="noopener noreferrer">→ Twitter</a>
                <a className="text-muted-foreground hover:text-foreground" href={personal.social.instagram} target="_blank" rel="noopener noreferrer">→ Instagram</a>
              </div>
            </div>
          </motion.div>

          <motion.form
            initial={{ opacity: 0, x: 20 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, amount: 0.3 }}
            transition={{ duration: 0.4 }}
            onSubmit={handleSubmit}
            className="space-y-4 rounded-2xl border border-border bg-card/60 p-6 sm:p-8"
          >
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Nama" name="name" placeholder="Nama Anda" required />
              <Field label="Email" name="email" type="email" placeholder="anda@email.com" required />
            </div>
            <Field label="Subjek" name="subject" placeholder="Tentang proyek apa?" required />
            <div>
              <label htmlFor="message" className="mb-1.5 block text-xs font-medium text-muted-foreground">
                Pesan
              </label>
              <textarea
                id="message"
                name="message"
                rows={5}
                required
                placeholder="Ceritakan proyek atau pertanyaan Anda..."
                className="w-full rounded-xl border border-input bg-background/60 px-3.5 py-2.5 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary"
              />
            </div>

            {status === "success" && (
              <div className="flex items-center gap-2 rounded-lg bg-emerald-500/10 px-3 py-2 text-sm text-emerald-600 dark:text-emerald-400">
                <CheckCircle2 className="size-4" /> Pesan berhasil terkirim. Terima kasih!
              </div>
            )}
            {status === "error" && (
              <div className="rounded-lg bg-red-500/10 px-3 py-2 text-sm text-red-600 dark:text-red-400">
                {error}
              </div>
            )}

            <button
              type="submit"
              disabled={status === "loading"}
              className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-foreground px-5 py-2.5 text-sm font-medium text-background transition-opacity hover:opacity-90 disabled:opacity-60 sm:w-auto"
            >
              {status === "loading" ? (
                <>
                  <Loader2 className="size-4 animate-spin" /> Mengirim...
                </>
              ) : (
                <>
                  Kirim pesan <Send className="size-4" />
                </>
              )}
            </button>
          </motion.form>
        </div>
      </div>
    </section>
  );
}

function ContactItem({
  icon: Icon,
  label,
  value,
  href,
}: {
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  value: string;
  href?: string;
}) {
  const inner = (
    <div className="flex items-center gap-4 rounded-2xl border border-border bg-card/60 p-4 transition-colors hover:bg-muted">
      <div className="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-accent text-primary-foreground">
        <Icon className="size-4" />
      </div>
      <div>
        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">{label}</p>
        <p className="text-sm font-medium">{value}</p>
      </div>
    </div>
  );
  return href ? (
    <a href={href} className="block">
      {inner}
    </a>
  ) : (
    inner
  );
}

function Field({
  label,
  name,
  type = "text",
  placeholder,
  required,
}: {
  label: string;
  name: string;
  type?: string;
  placeholder?: string;
  required?: boolean;
}) {
  return (
    <div>
      <label htmlFor={name} className="mb-1.5 block text-xs font-medium text-muted-foreground">
        {label}
      </label>
      <input
        id={name}
        name={name}
        type={type}
        placeholder={placeholder}
        required={required}
        className="w-full rounded-xl border border-input bg-background/60 px-3.5 py-2.5 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary"
      />
    </div>
  );
}
