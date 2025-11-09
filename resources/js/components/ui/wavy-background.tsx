import { cn } from "@/lib/utils";
import React, { useEffect, useRef, useState, type CSSProperties } from "react";
import { createNoise3D } from "simplex-noise";

export const WavyBackground = ({
  children,
  className,
  containerClassName,
  colors,
  waveWidth,
  backgroundFill,
  blur = 10,
  speed = "fast",
  waveOpacity = 0.5,
  ...props
}: {
  children?: any;
  className?: string;
  containerClassName?: string;
  colors?: string[];
  waveWidth?: number;
  backgroundFill?: string;
  blur?: number;
  speed?: "slow" | "fast";
  waveOpacity?: number;
  [key: string]: any;
}) => {
  const noise = createNoise3D();
  let w: number,
    h: number,
    nt: number,
    i: number,
    x: number,
    ctx: any,
    canvas: any;
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);
  const getSpeed = () => {
    switch (speed) {
      case "slow":
        return 0.001;
      case "fast":
        return 0.002;
      default:
        return 0.001;
    }
  };

  const updateCanvasSize = () => {
    if (canvas && containerRef.current) {
      const container = containerRef.current;
      const rect = container.getBoundingClientRect();
      w = ctx.canvas.width = rect.width;
      h = ctx.canvas.height = rect.height;
      ctx.filter = `blur(${blur}px)`;
    }
  };

  const init = () => {
    canvas = canvasRef.current;
    if (!canvas) return;
    ctx = canvas.getContext("2d");
    updateCanvasSize();
    nt = 0;
    window.onresize = function () {
      updateCanvasSize();
    };
    render();
  };

  const waveColors = colors ?? [
    "#38bdf8",
    "#818cf8",
    "#c084fc",
    "#e879f9",
    "#22d3ee",
  ];
  const drawWave = (n: number) => {
    nt += getSpeed();
    for (i = 0; i < n; i++) {
      ctx.beginPath();
      ctx.lineWidth = waveWidth || 50;
      ctx.strokeStyle = waveColors[i % waveColors.length];
      for (x = 0; x < w; x += 5) {
        var y = noise(x / 800, 0.3 * i, nt) * 100;
        ctx.lineTo(x, y + h * 0.5);
      }
      ctx.stroke();
      ctx.closePath();
    }
  };

  let animationId: number;
  const render = () => {
    ctx.fillStyle = backgroundFill || "black";
    ctx.globalAlpha = waveOpacity || 0.5;
    ctx.fillRect(0, 0, w, h);
    drawWave(5);
    animationId = requestAnimationFrame(render);
  };

  useEffect(() => {
    // Small delay to ensure container is rendered and measured
    const timer = setTimeout(() => {
      init();
    }, 100);
    return () => {
      clearTimeout(timer);
      cancelAnimationFrame(animationId);
    };
  }, []);

  const [isSafari, setIsSafari] = useState(false);
  useEffect(() => {
    setIsSafari(
      typeof window !== "undefined" &&
        navigator.userAgent.includes("Safari") &&
        !navigator.userAgent.includes("Chrome")
    );
  }, []);

  const canvasStyle: CSSProperties = isSafari ? { filter: `blur(${blur}px)` } : {};

  return React.createElement(
    "div",
    {
      ref: containerRef,
      className: cn(
        "min-h-[400px] flex flex-col items-center justify-center relative",
        containerClassName
      ),
    },
    React.createElement("canvas", {
      className: "absolute inset-0 z-0",
      ref: canvasRef,
      id: "canvas",
      style: canvasStyle,
    }),
    React.createElement(
      "div",
      {
        className: cn("relative z-10", className),
        ...props,
      },
      children
    )
  );
};
